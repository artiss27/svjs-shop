<?php

namespace App\Tests\Integration\Security\Verifier;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Verifier\EmailVerifier;
use App\Tests\TestUtils\Fixtures\UserFixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailSignatureComponents;

/**
 * @group integration
 */
class EmailVerifierTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private                $entityManager;
    private UserRepository $userRepository;
    private EmailVerifier  $emailVerifier;

    public function setUp(): void
    {
        parent::setUp();
        $kernel               = self::bootKernel();
        $this->entityManager  = $kernel->getContainer()->get('doctrine')->getManager();
        $this->emailVerifier  = static::getContainer()->get(EmailVerifier::class);
        $this->userRepository = $this->entityManager->getRepository(User::class);
    }

    public function testGenerateEmailSignature(): void
    {
        $user = $this->userRepository->findOneBy(['email' => UserFixtures::USER['email']]);
        $user->setIsVerified(false);

        $currentDatetime = new \DateTimeImmutable();
        $emailSignature  = $this->emailVerifier->generateEmailSignature('app_verify_email', $user);
        $this->assertGreaterThan($currentDatetime, $emailSignature->getExpiresAt());
    }

    public function testHandleEmailConfirmation(): void
    {
        $user = $this->userRepository->findOneBy(['email' => UserFixtures::USER['email']]);
        $user->setIsVerified(false);

        $emailSignature = $this->emailVerifier->generateEmailSignature('app_verify_email', $user);
        $this->emailVerifier->handleEmailConfirmation($emailSignature->getSignedUrl(), $user);
        $this->assertTrue($user->isVerified());
    }

    public function testGenerateEmailSignatureAndHandleEmailConfirmation()
    {
        $user = $this->userRepository->findOneBy(['email' => UserFixtures::USER['email']]);
        $user->setIsVerified(false);

        $emailSignature = $this->checkGenerateEmailSignature($user);
        $this->checkHandleEmailConfirmation($emailSignature, $user);
    }

    private function checkGenerateEmailSignature(User $user): VerifyEmailSignatureComponents
    {
        $currentDatetime = new \DateTimeImmutable();
        $emailSignature  = $this->emailVerifier->generateEmailSignature('app_verify_email', $user);
        $this->assertGreaterThan($currentDatetime, $emailSignature->getExpiresAt());

        return $emailSignature;
    }

    private function checkHandleEmailConfirmation(VerifyEmailSignatureComponents $signatureComponents, User $user): void
    {
        $this->assertFalse($user->isVerified());
        $this->emailVerifier->handleEmailConfirmation($signatureComponents->getSignedUrl(), $user);
        $this->assertTrue($user->isVerified());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}

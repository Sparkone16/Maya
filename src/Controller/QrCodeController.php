<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\Font\OpenSans;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleAuthenticatorTwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class QrCodeController extends AbstractController
{
    #[Route('/user/qr/{id<\d+>}', name: 'qr_code_ga')]
    public function afficherGoogleAuthenticatorQrCode(User $user, GoogleAuthenticatorInterface $googleAuthenticator): Response
    {
        if (!($user instanceof GoogleAuthenticatorTwoFactorInterface)) {
            throw new NotFoundHttpException('Impossible d\'afficher le QR code');
        }

        return $this->afficherQrCode($googleAuthenticator->getQRContent($user));
    }

    private function afficherQrCode(string $qrCodeContent): Response
    {
        $builder = new Builder(
            writer: new PngWriter(),    // rédacteur au format png
            writerOptions: [],
            data: $qrCodeContent,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 200,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
<<<<<<< HEAD
            // logoPath: $this->getParameter('kernel.project_dir').'/assets/img/maya1.png',
            // logoResizeToWidth: 50,
            // logoPunchoutBackground: true,
=======
<<<<<<< HEAD
            // logoPath: $this->getParameter('kernel.project_dir').'/assets/img/maya1.png',
            // logoResizeToWidth: 50,
            // logoPunchoutBackground: true,
=======
            logoPath: $this->getParameter('kernel.project_dir').'/assets/img/maya1.png',
            logoResizeToWidth: 20,
            logoPunchoutBackground: true,
>>>>>>> 49ea898134be55212383b64dc2ff7db38b10fce1
>>>>>>> main
            labelText: 'La ferme Maya',
            labelFont: new OpenSans(20),
            labelAlignment: LabelAlignment::Center
        );

        $result = $builder->build();
        return new Response($result->getString(), 200, ['Content-Type' => 'image/png']);
    }
}

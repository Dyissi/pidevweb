<?php
namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;

class ForgotPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, LoggerInterface $logger): Response
    {
        $error = null;
        $success = null;
        $showOtpField = false;

        if ($request->isMethod('POST')) {
            $phoneNumber = $request->request->get('phone_number');
            $submittedOtp = $request->request->get('otp');

            if ($phoneNumber && !$submittedOtp) {
                // Handle phone number submission
                if (!preg_match('/^\+[1-9]\d{1,14}$/', $phoneNumber)) {
                    $error = 'Please enter a valid phone number (e.g., +21627100103).';
                } else {
                    $user = $entityManager->getRepository(User::class)->findOneBy(['user_nbr' => $phoneNumber]);
                    if (!$user) {
                        $error = 'No account found with this phone number.';
                    } else {
                        $otp = random_int(100000, 999999);
                        $session->set('reset_otp', $otp);
                        $session->set('reset_phone_number', $phoneNumber);
                        $session->set('otp_expiry', time() + 300);

                        try {
                            $client = new Client();
                            $response = $client->post('https://api.infobip.com/sms/2/text/advanced', [
                                'headers' => [
                                    'Authorization' => 'App 61c6081f58caf63e486745d5bab5d94e-8d3606d0-c309-4ef1-867f-98eb0eca04ae',
                                    'Content-Type' => 'application/json',
                                    'Accept' => 'application/json',
                                ],
                                'json' => [
                                    'messages' => [
                                        [
                                            'from' => '+447491163443',
                                            'destinations' => [['to' => $phoneNumber]],
                                            'text' => "Your password reset code is: $otp",
                                        ],
                                    ],
                                ],
                            ]);
                            $success = 'OTP sent to your phone number.';
                            $logger->info('OTP sent via direct API', ['user_nbr' => $phoneNumber, 'otp' => $otp, 'response' => $response->getBody()->getContents()]);
                            $showOtpField = true;
                        } catch (\Exception $e) {
                            $error = 'Failed to send OTP: ' . $e->getMessage();
                            $logger->error('Direct API OTP sending failed', [
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                                'user_nbr' => $phoneNumber
                            ]);
                        }
                    }
                }
            } elseif ($submittedOtp) {
                // Handle OTP verification
                $storedOtp = $session->get('reset_otp');
                $otpExpiry = $session->get('otp_expiry');
                $phoneNumber = $session->get('reset_phone_number');

                if (!$storedOtp || !$otpExpiry || time() > $otpExpiry) {
                    $error = 'OTP has expired or is invalid. Please request a new one.';
                    $session->remove('reset_otp');
                    $session->remove('reset_phone_number');
                    $session->remove('otp_expiry');
                } elseif ($submittedOtp !== (string)$storedOtp) {
                    $error = 'Invalid OTP. Please try again.';
                    $showOtpField = true;
                } else {
                    $success = 'OTP verified successfully.';
                    return $this->redirectToRoute('app_reset_password');
                }
            }
        }

        return $this->render('forgot_password/index.html.twig', [
            'error' => $error,
            'success' => $success,
            'show_otp_field' => $showOtpField,
            'phone_number' => $phoneNumber ?? null,
        ]);
    }

    #[Route('/reset-password', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, UserPasswordHasherInterface $passwordHasher): Response
    {
        $error = null;
        $success = null;

        if ($request->isMethod('POST')) {
            $phoneNumber = $session->get('reset_phone_number');
            $newPassword = $request->request->get('new_password');

            if (!$phoneNumber) {
                $error = 'Session expired. Please start the reset process again.';
                return $this->redirectToRoute('app_forgot_password');
            }

            if (strlen($newPassword) < 6) {
                $error = 'Password must be at least 6 characters long.';
            } else {
                $user = $entityManager->getRepository(User::class)->findOneBy(['user_nbr' => $phoneNumber]);
                if ($user) {
                    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                    $user->setUserPwd($hashedPassword);
                    $entityManager->flush();

                    $success = 'Password reset successfully.';
                    $session->remove('reset_otp');
                    $session->remove('reset_phone_number');
                    $session->remove('otp_expiry');
                    return $this->redirectToRoute('app_login');
                } else {
                    $error = 'User not found.';
                }
            }
        }

        return $this->render('forgot_password/reset_password.html.twig', [
            'error' => $error,
            'success' => $success,
        ]);
    }
}
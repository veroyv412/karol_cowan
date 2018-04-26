<?php
// Require the Swift Mailer library
require_once 'lib/swift_required.php';

$transport = new Swift_SmtpTransport('smtp.gmail.com', 465, 'ssl');
$transport->setUsername('veroyv412@gmail.com');
$transport->setPassword('v3r0n1c4');


$mailer = new Swift_Mailer($transport);
$message = (new Swift_Message('Contacto Pagina Alquiler'))
    ->setFrom('veroyv412@gmail.com', 'Pagina Web')
    ->setContentType('text/html')
    ->setTo(array('veroyv412@gmail.com' => 'Veronica Nisenbaum'))
    ->setBody(json_encode($_POST));
$numSent = $mailer->send($message);

header('Location: /alquiler-thankyou.html');
exit();
<?php
function MailRegMsg($link)
{
	$to = 'msg7086@gmail.com';
	$subject = 'Register Confirmation';

	$message = '
Your registration request has been submitted to our system.
我们已收到您的账号注册申请。

We need to confirm your e-mail address now.
我们需要确认注册所使用的邮箱地址是由您本人所拥有。

If you, the owner of this mailbox, have just registered on our system, please click the link below. If not, please just ignore this e-mail.
若您，此邮箱地址的拥有者，刚刚在我们的系统上注册了账号，请点击下方的链接。若您对此事不知情，请您忽略此封邮件。

' . $link . '

This e-mail is sent by system automatically. Please do not reply directly to this e-mail.
此封邮件是由系统自动发送的。请不要直接回复此邮件。


';

	// 当发送 HTML 电子邮件时，请始终设置 content-type
	//$headers = "MIME-Version: 1.0" . "\r\n";
	$headers = "Content-type:text/plain;charset=utf8" . "\r\n";

	// 更多报头
	$headers .= 'From: Register Confirmation <reg.confirm@popro.info>' . "\r\n";

	mail($to,$subject,$message,$headers);
}

//MailRegMsg('http://popro.info/user.php?confirmcode=' . md5(time()));

<?php
require_once __DIR__.'/helpers.php';$u=current_user();$data=input();
try{$name=trim((string)($data['name']??($u?display_name($u):'')));$email=trim((string)($data['email']??($u['email']??'')));$subject=trim((string)($data['subject']??''));$message=trim((string)($data['message']??''));if(!$message)fail('Message is required.');$st=db()->prepare("INSERT INTO support_messages(user_id,customer_name,email,subject,message,status) VALUES(?,?,?,?,?,'open')");$st->execute([$u['id']??null,$name,$email,$subject,$message]);respond(['ok'=>true]);}catch(Throwable $e){fail($e->getMessage(),500);}

<?php
require_once __DIR__.'/helpers.php';$u=require_role(['customer']);$data=input();$uid=(int)$u['id'];
try{
if($_SERVER['REQUEST_METHOD']==='GET')respond(['ok'=>true,'settings'=>customer_settings($uid)]);
$current=customer_settings($uid);$next=array_replace_recursive($current,$data);
$sql='INSERT INTO customer_settings(user_id,order_notifications,deal_notifications,recommendation_notifications,email_communication,sms_communication,marketing_communication,personalization,analytics,appearance) VALUES(?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE order_notifications=VALUES(order_notifications),deal_notifications=VALUES(deal_notifications),recommendation_notifications=VALUES(recommendation_notifications),email_communication=VALUES(email_communication),sms_communication=VALUES(sms_communication),marketing_communication=VALUES(marketing_communication),personalization=VALUES(personalization),analytics=VALUES(analytics),appearance=VALUES(appearance)';
$st=db()->prepare($sql);$st->execute([$uid,(int)$next['notifications']['orders'],(int)$next['notifications']['deals'],(int)$next['notifications']['recommendations'],(int)$next['communications']['email'],(int)$next['communications']['sms'],(int)$next['communications']['marketing'],(int)$next['privacy']['personalization'],(int)$next['privacy']['analytics'],in_array($next['appearance'],['system','light','dark'],true)?$next['appearance']:'system']);respond(['ok'=>true,'settings'=>customer_settings($uid)]);
}catch(Throwable $e){fail($e->getMessage(),500);}

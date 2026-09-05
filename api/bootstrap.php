<?php
require_once __DIR__.'/helpers.php';
try {
    $u=current_user();
    $role=$u['role']??null;
    $customers=[];$orders=[];$cart=[];$viewed=[];$purchased=[];$search=[];
    if($u){
        if($role==='customer'){
            $customers=[array_merge(profile_shape($u),['settings'=>customer_settings((int)$u['id'])])];
            $orders=order_rows((int)$u['id']);
        } elseif(in_array($role,['admin','owner'],true)) {
            $customers=array_map('profile_shape',db()->query("SELECT id,username,role,first_name,last_name,email,phone,address,city,postal_code,country,status,created_at FROM users WHERE role='customer' ORDER BY id DESC")->fetchAll());
            $orders=order_rows(null);
        }
        $cart=cart_shape((int)$u['id']);
        $st=db()->prepare("SELECT product_id FROM product_activity WHERE user_id=? AND activity_type='view' GROUP BY product_id ORDER BY MAX(id) DESC LIMIT 12");$st->execute([$u['id']]);$viewed=array_map('strval',array_column($st->fetchAll(),'product_id'));
        $st=db()->prepare("SELECT product_id FROM product_activity WHERE user_id=? AND activity_type='purchase' GROUP BY product_id ORDER BY MAX(id) DESC LIMIT 24");$st->execute([$u['id']]);$purchased=array_map('strval',array_column($st->fetchAll(),'product_id'));
        $st=db()->prepare('SELECT search_term FROM search_history WHERE user_id=? ORDER BY id DESC LIMIT 12');$st->execute([$u['id']]);$search=array_column($st->fetchAll(),'search_term');
    }
    respond(['ok'=>true,'user'=>session_shape($u),'currentProfile'=>$u?profile_shape($u):null,'products'=>product_rows(),'categories'=>category_names(),'rules'=>rule_rows(),'customers'=>$customers,'orders'=>$orders,'cart'=>$cart,'viewed'=>$viewed,'purchased'=>$purchased,'searchHistory'=>$search]);
} catch(Throwable $e){ fail('Database connection or schema error: '.$e->getMessage(),500); }

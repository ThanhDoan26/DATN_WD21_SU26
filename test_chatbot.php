<?php
$user = \App\Models\User::first();
$request = \Illuminate\Http\Request::create('/api/ai/chat', 'POST', ['message' => 'Rạp đang có phim gì chiếu?']);
$request->headers->set('Accept', 'application/json');
$request->setUserResolver(fn() => $user);
echo app()->handle($request)->getContent();

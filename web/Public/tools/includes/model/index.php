<?php

use Framework\Components\User;
use Framework\Models\Database\Db;

$user = User::create();
$userId = $user->getId();

if (!Empty($_POST['userId'])) {
    $userId = (int)($_POST['userId'] ?? 0);
}

if (!Empty($_POST['userCode'])) {
    $userId = (int)($_POST['userCode'] ?? '');
}

if (!Empty($_POST['delete-user-cache']) && $userId) {
    $user->clearUserDataCache($userId);

    $data['success'] = 'User profile (ID: ' . $userId . ') is deleted successfully!';
}

if (!Empty($_POST['delete-all-user-cache'])) {
    $result = Db::create()->getRows(
        Db::select(
            'users',
            [
                'us_id'
            ]
        )
    );
    if($result) {
        foreach($result AS $row){
            $user->clearUserDataCache($row['us_id']);
        }

        $data['success'] = 'All user profiles are deleted successfully!';
    }
}

$data['userId'] = $userId;
$data['user'] = $user->getUserProfile($userId);

<?php
include 'db.php';

function getGlobalSetting($key) {
    global $conn;
    $query = $conn->prepare("SELECT setting_value FROM referral_settings WHERE setting_key = ?");
    $query->bind_param("s", $key);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['setting_value'] : null;
}

function getUserCommission($userId) {
    global $conn;
    $query = $conn->prepare("SELECT commission_value FROM user_commissions WHERE user_id = ?");
    $query->bind_param("i", $userId);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['commission_value'] : null;
}

function checkMinimumDeposit($userId, $minDeposit) {
    global $conn;
    $query = $conn->prepare("SELECT SUM(value) as total_deposit FROM deposits WHERE user_id = ? AND status = 'confirmed'");
    $query->bind_param("i", $userId);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    return $row['total_deposit'] >= $minDeposit;
}

function validateDeposits() {
    global $conn;
    $globalMinDeposit = getGlobalSetting('min_deposit');
    $globalCommission = getGlobalSetting('commission_value');

    $referralQuery = $conn->prepare("SELECT r.referred_user_id, r.user_id FROM referrals r JOIN users u ON r.referred_user_id = u.id WHERE r.status != 'deposited'");
    $referralQuery->execute();
    $result = $referralQuery->get_result();

    while ($referral = $result->fetch_assoc()) {
        $referredUserId = $referral['referred_user_id'];
        $referrerId = $referral['user_id'];
        $userCommission = getUserCommission($referrerId);

        if ($userCommission !== null) {
            $commissionValue = $userCommission;
            $minDeposit = $globalMinDeposit;
        } else {
            $commissionValue = $globalCommission;
            $minDeposit = $globalMinDeposit;
        }

        if (checkMinimumDeposit($referredUserId, $minDeposit)) {
            $commissionAmount = $commissionValue;

            $updateReferralQuery = $conn->prepare("UPDATE referrals SET status = 'deposited', referral_amount = ?, final_referral_amount = ? WHERE referred_user_id = ?");
            $updateReferralQuery->bind_param("ddi", $commissionAmount, $commissionAmount, $referredUserId);
            $updateReferralQuery->execute();
        }
    }
}

function userExists($userId) {
    global $conn;
    $query = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $query->bind_param("i", $userId);
    $query->execute();
    $result = $query->get_result();
    return $result->num_rows > 0;
}
?>

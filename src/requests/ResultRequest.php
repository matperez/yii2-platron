<?php
namespace matperez\yii2platron\requests;

use yii\base\Model;

class ResultRequest extends Model
{
    public $pg_order_id;

    public $pg_payment_id;

    public $pg_amount;

    public $pg_currency;

    public $pg_net_amount;

    public $pg_ps_amount;

    public $pg_ps_full_amount;

    public $pg_ps_currency;

    public $pg_overpayment;

    public $pg_payment_system;

    public $pg_result;

    public $pg_payment_date;

    public $pg_can_reject;

    public $pg_card_brand;

    public $pg_card_hash;

    public $pg_auth_code;

    public $pg_captured;

    public $pg_user_phone;

    public $pg_need_phone_notification;

    public $pg_user_contact_email;

    public $pg_need_email_notification;

    public $pg_failure_code;

    public $pg_recurring_profile_id;

    public $pg_recurring_profile_expiry_date;

    public $pg_salt;

    public $pg_sig;
}

<?php
declare(strict_types=1);

namespace Ekvio\Integration\Invoker\Report;

/**
 * Class ReportErrorsHeader
 * @package App
 */
class ReportError
{
    /**
     * @var string
     */
    private const UNKNOWN_ERROR = 'UNKWN_ERR';
    /**
     * @var bool
     */
    private $logUnknownMessage = false;
    /**
     * @var array|string[]
     */
    private $errorMap = [
        'login_login_required' => 'LOGIN_NVALID',
        'login_значение_логин_неверно' => 'LOGIN_NVALID',
        'login_login_is_invalid' => 'LOGIN_NVALID',
        'password_пароль_не_соответствует_формату' => 'PASSW_NVALID',
        'password_password_doesn_t_match_format' => 'PASSW_NVALID',
        'email_value_is_not_unique' => 'EMAIL_NUNIQ',
        'email_значение_e_mail_не_является_правильным_email_адресом' => 'EMAIL_NVALID',
        'email_email_required' => 'EMAIL_EMPT',
        'phone_phone_required' => 'PHONE_NVALID',
        'phone_value_is_not_unique' => 'PHONE_NUNIQ',
        'phone_incorrect_phone' => 'PHONE_NVALID',
        'email_e_mail_is_not_a_valid_email_address' => 'EMAIL_NVALID',
        'chief_email_manager_s_e_mail_is_not_a_valid_email_address' => 'CHIEF_EMAIL_NVALID',
        'region_group_is_empty' => 'RGN_EMPT',
        'groups_group_region_is_required_and_not_blank' => 'RGN_EMPT',
        'city_group_is_empty' => 'CITY_EMPT',
        'groups_group_city_is_required_and_not_blank' => 'CITY_EMPT',
        'position_city_is_invalid' => 'CITY_NVALID',
        'role_group_is_empty' => 'ROLE_EMPT',
        'groups_group_role_is_required_and_not_blank' => 'ROLE_EMPT',
        'position_group_is_empty' => 'PSTN_EMPT',
        'groups_group_position_is_required_and_not_blank' => 'PSTN_EMPT',
        'team_group_is_empty' => 'TEAM_EMPT',
        'groups_group_team_is_required_and_not_blank' => 'TEAM_EMPT',
        'assignment_group_is_empty' => 'ASSGN_EMPT',
        'groups_group_assignment_is_required_and_not_blank' => 'ASSGN_EMPT',
        'fname_значение_name_должно_содержать_максимум_50_символов' => 'FIRST_NAME_NVALID',
        'fname_name_should_contain_at_most_50_characters' => 'FIRST_NAME_NVALID',
        'first_name_first_name_required' => 'FIRST_NAME_EMPT',
        'last_name_last_name_required' => 'LAST_NAME_EMPT',
        'first_name_incorrect_data_format_please_try_again' => 'FIRST_NAME_NVALID',
        'first_name_first_name_must_be_cyrillic' => 'FIRST_NAME_NVALID',
        'last_name_incorrect_data_format_please_try_again' => 'LAST_NAME_NVALID',
        'last_name_last_name_must_be_cyrillic' => 'LAST_NAME_NVALID',
        'login_login_already_exists' => 'DUBLICAT',
        'phone_phone_number_must_be_min_10_numbers' => 'PHONE_NVALID',
        'email_email_is_not_valid' => 'EMAIL_NVALID',
        'chief_email_manager_s_e_mail_не_является_правильным_email_адресом' => 'CHIEF_EMAIL_NVALID',
        'chief_email_email_is_not_valid' => 'CHIEF_EMAIL_NVALID',
        'groups_group_department_is_required_and_not_blank' => 'DEPARTMENT_EMPT',
    ];

    /**
     * ReportErrorsHeader constructor.
     * @param array $errorMap
     * @param array $config
     */
    public function __construct(array $errorMap = [], array $config = [])
    {
        if($errorMap) {
            $this->errorMap = array_merge($this->errorMap, $errorMap);
        }

        if(isset($config['logUnknownMessage']) && is_bool($config['logUnknownMessage'])) {
            $this->logUnknownMessage = $config['logUnknownMessage'];
        }
    }

    /**
     * @return array
     */
    public function errors(): array
    {
        $this->errorMap[] = self::UNKNOWN_ERROR;
        return array_unique(array_values($this->errorMap));
    }

    /**
     * @param string $field
     * @param string $message
     * @return string
     */
    public function getError(string $field, string $message): string
    {
        $replacedMsg = trim(preg_replace("/([ '.,\"\-«»]+)/u", '_', $message), '_');

        $key = sprintf('%s_%s',
            mb_strtolower($field),
            mb_strtolower($replacedMsg)
        );

        if($this->logUnknownMessage && !isset($this->errorMap[$key])) {
            fwrite(STDOUT, sprintf('Undefined error validation key: field [%s], message [%s], key [%s]' . PHP_EOL, $field, $message, $key));
        }

        return $this->errorMap[$key] ?? self::UNKNOWN_ERROR;
    }
}
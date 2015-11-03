<?php
namespace WeDevs\ERP\HRM;

use \WeDevs\ERP\Framework\Traits\Hooker;
use \WeDevs\ERP\HRM\Models\announcment;
use \WeDevs\ERP\HRM\Models\employee;
use \WeDevs\ERP\HRM\Models\Dependents;
use \WeDevs\ERP\HRM\Models\Designation;

/**
 * Ajax handler
 *
 * @package WP-ERP
 */
class Hr_Log {

    use Hooker;

    /**
     * Load autometically when class inistantiate
     *
     * @since 0.1
     *
     * @return void
     */
    public function __construct() {

        // Department
        $this->action( 'erp_hr_dept_new', 'create_department', 10, 2 );
        $this->action( 'erp_hr_dept_delete', 'delete_department', 10 );
        $this->action( 'erp_hr_dept_before_updated', 'update_department', 10, 2 );

        // Designation
        $this->action( 'erp_hr_desig_new', 'create_designation', 10, 2 );
        $this->action( 'erp_hr_desig_delete', 'delete_designation', 10 );
        $this->action( 'erp_hr_desig_before_updated', 'update_designation', 10, 2 );

        //Leave Policy @TODO Update Policy
        $this->action( 'erp_hr_leave_policy_new', 'create_policy', 10, 2 );
        $this->action( 'erp_hr_leave_policy_delete', 'delete_policy', 10 );
        $this->action( 'erp_hr_leave_before_policy_updated', 'update_policy', 10, 2 );

        //Holiday
        $this->action( 'erp_hr_new_holiday', 'create_holiday', 10, 2 );
        $this->action( 'erp_hr_leave_holiday_delete', 'delete_holiday', 10 );
        $this->action( 'erp_hr_before_update_holiday', 'update_holiday', 10, 2 );
    }

    /**
     * Get different array from two array
     *
     * @since 0.1
     *
     * @param  array $new_data
     * @param  array $old_data
     *
     * @return array
     */
    public function get_array_diff( $new_data, $old_data, $is_seriazie = false ) {

        $old_value = $new_value = [];
        $changes_key = array_keys( array_diff_assoc( $new_data, $old_data ) );

        foreach ( $changes_key as $key => $change_field_key ) {
            $old_value[$change_field_key] = $old_data[$change_field_key];
            $new_value[$change_field_key] = $new_data[$change_field_key];
        }

        if ( ! $is_seriazie ) {
            return [
                'new_val' => $new_value ? base64_encode( maybe_serialize( $new_value ) ) : '',
                'old_val' => $old_value ? base64_encode( maybe_serialize( $old_value ) ) : ''
            ];
        } else {
            return [
                'new_val' => $new_value,
                'old_val' => $old_value
            ];
        }
    }

    /**
     * Add log when new department created
     *
     * @since 0.1
     *
     * @param  integer $dept_id
     * @param  array $fields
     *
     * @return void
     */
    public function create_department( $dept_id, $fields ) {
        erp_log()->add([
            'sub_component' => 'department',
            'message'       => sprintf( '%s department has been created', $fields['title'] ),
            'created_by'    => get_current_user_id()
        ]);
    }

    /**
     * Add log when department deleted
     *
     * @since 0.1
     *
     * @param  integer $dept_id
     *
     * @return void
     */
    public function delete_department( $dept_id ) {

        if ( ! $dept_id ) {
            return;
        }

        $department = new \WeDevs\ERP\HRM\Department( intval( $dept_id ) );

        erp_log()->add([
            'sub_component' => 'department',
            'message'       => sprintf( '%s department has been deleted', $department->title ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ]);
    }

    /**
     * Add log when udpate department
     *
     * @since 0.1
     *
     * @param  integer $dept_id
     * @param  array $fields
     *
     * @return void
     */
    public function update_department( $dept_id, $fields ) {

        if ( ! $dept_id ) {
            return;
        }

        $old_department = \WeDevs\ERP\HRM\Models\Department::find( $dept_id )->toArray();
        unset( $old_department['created_at'], $old_department['updated_at'] );

        $changes = $this->get_array_diff( $fields, $old_department, true );

        if ( empty( $changes['old_val'] ) && empty( $changes['new_val'] ) ) {
            $message = __( 'No Changes', 'wp-erp' );
        } else {
            array_walk ( $changes, function ( &$key ) {
                if ( isset( $key['lead'] ) ) {
                    if( $key['lead'] ) {
                        $employee = new \WeDevs\ERP\HRM\Employee( intval( $key['lead'] ) );
                        $key['department_lead'] = $employee->get_full_name();
                    } else {
                        $key['department_lead'] = 'No deparment leader';
                    }
                    unset( $key['lead'] );
                }

                if ( isset( $key['parent'] ) ) {
                    if( $key['parent'] ) {
                        $department = new \WeDevs\ERP\HRM\Department( intval( $key['parent'] ) );
                        $key['parent_department'] = $department->title;
                    } else {
                        $key['parent_department'] = 'No Parent Department';
                    }
                    unset( $key['parent'] );
                }
            } );

            $message = sprintf( '%s department has been edited', $old_department['title'] );
        }

        erp_log()->add([
            'sub_component' => 'department',
            'message'       => $message,
            'created_by'    => get_current_user_id(),
            'changetype'    => 'edit',
            'old_value'     => $changes['old_val'] ? base64_encode( maybe_serialize( $changes['old_val'] ) ) : '',
            'new_value'     => $changes['new_val'] ? base64_encode( maybe_serialize( $changes['new_val'] ) ) : ''
        ]);

    }

    /**
     * Add log when new designation created
     *
     * @since 0.1
     *
     * @param  integer $desig_id
     * @param  array $fields
     *
     * @return void
     */
    public function create_designation( $desig_id, $fields ) {
        erp_log()->add([
            'sub_component' => 'designation',
            'message'       => sprintf( '%s designation has been created', $fields['title'] ),
            'created_by'    => get_current_user_id()
        ]);
    }

    /**
     * Add log when department deleted
     *
     * @since 0.1
     *
     * @param  integer $dept_id
     *
     * @return void
     */
    public function delete_designation( $desig ) {
        if ( ! $desig ) {
            return;
        }

        erp_log()->add([
            'sub_component' => 'designation',
            'message'       => sprintf( '%s designation has been deleted', $desig->title ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ]);
    }

    /**
     * Add log when designation updated
     *
     * @since 0.1
     *
     * @param  integer $desig_id
     * @param  array $fields
     *
     * @return void
     */
    public function update_designation( $desig_id, $fields ) {
        if ( ! $desig_id ) {
            return;
        }

        $old_desig = \WeDevs\ERP\HRM\Models\Designation::find( $desig_id )->toArray();
        unset( $old_desig['created_at'], $old_desig['updated_at'] );

        $changes = $this->get_array_diff( $fields, $old_desig );

        if ( empty( $changes['old_val'] ) && empty( $changes['new_val'] ) ) {
            $message = __( 'No Changes', 'wp-erp' );
        } else {
            $message = sprintf( '%s designation has been edited', $old_desig['title'] );
        }
        erp_log()->add([
            'sub_component' => 'designation',
            'message'       => $message,
            'created_by'    => get_current_user_id(),
            'changetype'    => 'edit',
            'old_value'     => $changes['old_val'],
            'new_value'     => $changes['new_val']
        ]);
    }

    /**
     * Logging for creating policy
     *
     * @since 0.1
     *
     * @param  integer $policy_id
     * @param  array $fields
     *
     * @return void
     */
    public function create_policy( $policy_id, $fields ) {

        if ( ! $policy_id ) {
            return;
        }

        erp_log()->add([
            'sub_component' => 'leave',
            'message'       => sprintf( '%s policy has been created', $fields['name'] ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'add',
        ]);
    }

    /**
     * Adding log when policy deleted
     *
     * @since 0.1
     *
     * @param  integer $policy_id
     *
     * @return void
     */
    public function delete_policy( $policy_id ) {

        if ( ! $policy_id ) {
            return;
        }

        $policy = \WeDevs\ERP\HRM\Models\Leave_Policies::find( $policy_id );

        if ( !$policy ) {
            return;
        }

        erp_log()->add([
            'sub_component' => 'leave',
            'message'       => sprintf( '%s policy has been deleted', $policy->name ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ]);
    }

    /**
     * Add log when udpate policy
     *
     * @since 0.1
     *
     * @param  integer $policy_id
     * @param  array $fields
     *
     * @return void
     */
    public function update_policy( $policy_id, $fields ) {

    }

    /**
     * Create hoiliday log
     *
     * @since 0.1
     *
     * @param  integer $holiday_id
     * @param  array $fields
     *
     * @return void
     */
    public function create_holiday( $holiday_id, $fields ) {

        if ( ! $holiday_id ) {
            return;
        }

        erp_log()->add([
            'sub_component' => 'leave',
            'message'       => sprintf( 'A new holiday named %s has been created', $fields['title'] ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'add',
        ]);
    }

    /**
     * Delete holiday log insert
     *
     * @since 0.1
     *
     * @param  integer $holiday_id
     *
     * @return void
     */
    public function delete_holiday( $holiday_id ) {

        if ( ! $holiday_id ) {
            return;
        }

        $holiday = \WeDevs\ERP\HRM\Models\Leave_Holiday::find( $holiday_id );

        if ( !$holiday ) {
            return;
        }

        erp_log()->add([
            'sub_component' => 'leave',
            'message'       => sprintf( '%s holiday has been deleted', $holiday->title ),
            'created_by'    => get_current_user_id(),
            'changetype'    => 'delete',
        ]);
    }

    /**
     * Add log when edit holiday
     *
     * @since 0.1
     *
     * @param  integer $holiday_id
     * @param  array $fields
     *
     * @return void
     */
    public function update_holiday( $holiday_id, $fields ) {

        if ( ! $holiday_id ) {
            return;
        }

        $old_holiday = \WeDevs\ERP\HRM\Models\Leave_Holiday::find( $holiday_id )->toArray();
        unset( $old_holiday['created_at'], $old_holiday['updated_at'] );

        $old_holiday['start'] = erp_format_date( $old_holiday['start'], 'Y-m-d' );
        $old_holiday['end']   = erp_format_date( $old_holiday['end'], 'Y-m-d' );

        $fields['start'] = erp_format_date( $fields['start'], 'Y-m-d' );
        $fields['end']   = erp_format_date( $fields['end'], 'Y-m-d' );

        $changes = $this->get_array_diff( $fields, $old_holiday, true );

        if ( empty( $changes['old_val'] ) && empty( $changes['new_val'] ) ) {
            $message = __( 'No Changes', 'wp-erp' );
        } else {
            array_walk ( $changes, function ( &$key ) {
                if ( isset( $key['start'] ) ) {
                    $key['start_date'] = erp_format_date( $key['start'] );
                    unset( $key['start'] );
                }

                if ( isset( $key['end'] ) ) {
                    $key['end_date'] = erp_format_date( $key['end'] );
                    unset( $key['end'] );
                }
            } );
            $message = sprintf( '%s holiday has been edited', $old_holiday['title'] );
        }

        erp_log()->add([
            'sub_component' => 'leave',
            'message'       => $message,
            'created_by'    => get_current_user_id(),
            'changetype'    => 'edit',
            'old_value'     => $changes['old_val'] ? base64_encode( maybe_serialize( $changes['old_val'] ) ) : '',
            'new_value'     => $changes['new_val'] ? base64_encode( maybe_serialize( $changes['new_val'] ) ) : ''
         ]);

    }

}
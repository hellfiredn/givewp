<?php
/**
 * Class KTech_Upgrade_Requests_DB
 * Handle database operations for upgrade requests
 */

if (!defined('ABSPATH')) {
    exit;
}

class KTech_Upgrade_Requests_DB {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'kam_upgrade_requests';
    }
    
    /**
     * Install table when plugin activates
     */
    public static function install_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'kam_upgrade_requests';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id int(11) NOT NULL,
            company_name varchar(255) NOT NULL,
            representative_name varchar(255) NOT NULL,
            business_address text NOT NULL,
            business_code varchar(50) NOT NULL,
            business_license_file varchar(255),
            contact_phone varchar(20) NOT NULL,
            current_package varchar(50) NOT NULL,
            requested_package varchar(50) NOT NULL,
            status varchar(20) DEFAULT 'pending',
            rejection_reason text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Insert new upgrade request
     */
    public function insert($data) {
        global $wpdb;
        
        $defaults = array(
            'user_id' => 0,
            'company_name' => '',
            'representative_name' => '',
            'business_address' => '',
            'business_code' => '',
            'business_license_file' => '',
            'contact_phone' => '',
            'current_package' => '',
            'requested_package' => '',
            'status' => 'pending'
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert(
            $this->table_name,
            $data,
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        return $result !== false ? $wpdb->insert_id : false;
    }
    
    /**
     * Update upgrade request
     */
    public function update($id, $data) {
        global $wpdb;
        
        $data['updated_at'] = current_time('mysql');
        
        $result = $wpdb->update(
            $this->table_name,
            $data,
            array('id' => $id),
            null,
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Delete upgrade request
     */
    public function delete($id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Delete all upgrade requests by user ID
     */
    public function delete_by_user($user_id) {
        global $wpdb;
        return $wpdb->delete($this->table_name, array('user_id' => $user_id), array('%d'));
    }
    
    /**
     * Get upgrade request by ID
     */
    public function get_by_id($id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        ));
    }
    
    /**
     * Get upgrade requests by user ID
     */
    public function get_by_user($user_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ));
    }
    
    /**
     * Get all upgrade requests with optional filters
     */
    public function get_all($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'status' => '',
            'limit' => 0,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = "1=1";
        $where_values = array();
        
        if (!empty($args['status'])) {
            $where .= " AND status = %s";
            $where_values[] = $args['status'];
        }
        
        $order_clause = sprintf("ORDER BY %s %s", 
            sanitize_sql_orderby($args['orderby']), 
            strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC'
        );
        
        $limit_clause = '';
        if ($args['limit'] > 0) {
            $limit_clause = sprintf("LIMIT %d", $args['limit']);
            if ($args['offset'] > 0) {
                $limit_clause .= sprintf(" OFFSET %d", $args['offset']);
            }
        }
        
        $sql = "SELECT ur.*, u.user_login, u.display_name, u.user_email 
                FROM {$this->table_name} ur 
                LEFT JOIN {$wpdb->users} u ON ur.user_id = u.ID 
                WHERE {$where} 
                {$order_clause} 
                {$limit_clause}";
        
        if (!empty($where_values)) {
            return $wpdb->get_results($wpdb->prepare($sql, $where_values));
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get pending upgrade requests
     */
    public function get_pending() {
        return $this->get_all(array('status' => 'pending'));
    }
    
    /**
     * Get approved upgrade requests
     */
    public function get_approved() {
        return $this->get_all(array('status' => 'approved'));
    }
    
    /**
     * Get rejected upgrade requests
     */
    public function get_rejected() {
        return $this->get_all(array('status' => 'rejected'));
    }
    
    /**
     * Approve upgrade request
     */
    public function approve($id) {
        return $this->update($id, array('status' => 'approved'));
    }
    
    /**
     * Reject upgrade request
     */
    public function reject($id, $reason = '') {
        return $this->update($id, array(
            'status' => 'rejected',
            'rejection_reason' => $reason
        ));
    }
    
    /**
     * Check if user has pending request
     */
    public function has_pending_request($user_id) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE user_id = %d AND status = 'pending'",
            $user_id
        ));
        
        return $count > 0;
    }
    
    /**
     * Get latest request by user
     */
    public function get_latest_by_user($user_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE user_id = %d ORDER BY created_at DESC LIMIT 1",
            $user_id
        ));
    }
    
    /**
     * Get count by status
     */
    public function get_count_by_status($status = '') {
        global $wpdb;
        
        if (empty($status)) {
            return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        }
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s",
            $status
        ));
    }
    
    /**
     * Get statistics
     */
    public function get_stats() {
        global $wpdb;
        
        $stats = $wpdb->get_results(
            "SELECT status, COUNT(*) as count 
             FROM {$this->table_name} 
             GROUP BY status"
        );
        
        $result = array(
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0
        );
        
        foreach ($stats as $stat) {
            $result[$stat->status] = (int) $stat->count;
            $result['total'] += (int) $stat->count;
        }
        
        return $result;
    }
    
    /**
     * Get requests created in last N days
     */
    public function get_recent($days = 7) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT ur.*, u.user_login, u.display_name, u.user_email 
             FROM {$this->table_name} ur 
             LEFT JOIN {$wpdb->users} u ON ur.user_id = u.ID 
             WHERE ur.created_at >= DATE_SUB(NOW(), INTERVAL %d DAY) 
             ORDER BY ur.created_at DESC",
            $days
        ));
    }
}
?>

<?php
/**
 * Service Email class
 * 
 * @author Callistus
 * @since 2.2.0
 * @package SmartWoo\Emails
 */

defined( 'ABSPATH' ) || exit;

class SmartWoo_Service_Mails extends SmartWoo_Mails {
    /**
     * Smart Woo Service
     * @var SmartWoo_Service $service
     */
    protected $service;

    /**
     * @var WC_Customer $client
     */
    protected $client;

    /**
     * Flag to check whether this class can send email
     */
    protected $object_ready = false;

    /**
     * Email body
     * 
     * @param string $body
     */
    protected $body;
}
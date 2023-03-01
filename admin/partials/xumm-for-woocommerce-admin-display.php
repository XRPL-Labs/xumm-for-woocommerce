<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://xumm.app/
 * @since      1.0.0
 *
 * @package    Xumm_For_Woocommerce
 * @subpackage Xumm_For_Woocommerce/admin/partials
 */

use Xrpl\XummSdkPhp\XummSdk;

$storeCurrency = get_woocommerce_currency();

if (!empty($context->api) && !empty($context->api_secret))
{
    $sdk = new XummSdk($context->api, $context->api_secret);
}

if (empty($context->api) || empty($context->api_secret)) {
    echo('<div class="notice notice-info is-dismissible"><p>'. __('Please add XUMM API keys from', 'xumm-for-woocommerce') .' <a href="https://apps.xumm.dev/">'. __('XUMM API', 'xumm-for-woocommerce') .'</a></p></div>');
} else
{
    try {
        $pong = $sdk->ping();

        if(!empty($pong->call->uuidV4)) {
            echo('<div class="notice notice-success is-dismissible"><p>'.__('Connected to the', 'xumm-for-woocommerce', 'xumm-for-woocommerce').' <a href="https://apps.xumm.dev/">'.__('XUMM API', 'xumm-for-woocommerce').'</a></p></div>');

            $webhookApi = $pong->application->webhookUrl;
            $webhook = get_home_url() . '/?wc-api='. $context->id;
            if($webhook != $webhookApi) echo('<div class="notice notice-error is-dismissible"><p>'.__('WebHook incorrect on', 'xumm-for-woocommerce').' <a href="https://apps.xumm.dev/">'.__('XUMM API', 'xumm-for-woocommerce').'</a>, '.__('should be', 'xumm-for-woocommerce').' '.$webhook.'</p></div>');
        }
        else echo('<div class="notice notice-error is-dismissible"><p>'.__('Connection API Error to the', 'xumm-for-woocommerce').' <a href="https://apps.xumm.dev/">'.__('XUMM API', 'xumm-for-woocommerce').'</a>. '. __('Check your API keys.', 'xumm-for-woocommerce') .'Error Code:'. $body['error']['code'].'</p></div>');

    } catch (\Exception $e) {
        echo ('<div class="notice notice-error is-dismissible"><p>'.__('Connection Error to the', 'xumm-for-woocommerce').' <a href="https://apps.xumm.dev/">'.__('XUMM API', 'xumm-for-woocommerce').'</a></p></div>');
    }

    if (!in_array($storeCurrency, $context->availableCurrencies)) echo('<div class="notice notice-error"><p>'.__('Please change store currency', 'xumm-for-woocommerce').'</p></div>');
    if (!empty($context->currencies) && !empty($context->issuers))
    {
        if ($storeCurrency != 'XRP' && $context->currencies != 'XRP' && $storeCurrency != $context->currencies) echo('<div class="notice notice-error"><p>'.__('Please change store currency', 'xumm-for-woocommerce').'</p></div>');
        if ($context->currencies != 'XRP' && empty($context->issuers) && get_woocommerce_currency() != 'XRP') echo('<div class="notice notice-error"><p>'.__('Please set the issuer and save changes', 'xumm-for-woocommerce').'</p></div>');
    }
}

?>
<h2><?php _e('XUMM Payment Gateway for WooCommerce','xumm-for-woocommerce'); ?></h2>

<?php if (empty($context->logged_in)): ?>
<p><?php _e('Welcome to the XUMM Payment Gateway for Woocommerce', 'xumm-for-woocommerce') ?></p>

<p><?php _e('In order to continue, you need to complete these steps:', 'xumm-for-woocommerce') ?></p>
<ol>
    <li><?php _e('Go to the', 'xumm-for-woocommerce') ?> <a target="_blank" href="https://apps.xumm.dev/"><?php _e('Xumm Developer Console', 'xumm-for-woocommerce') ?></a></li>
    <li><?php _e('Create your app to get your API Credentials', 'xumm-for-woocommerce') ?></li>
    <li><?php _e('Define your WebHook URL like this: ', 'xumm-for-woocommerce') ?> <a href="<?php echo site_url() ?>?wc-api=xumm"><?php echo site_url() ?>?wc-api=xumm</a></li>
    <li><?php _e('Place your API Credentials', 'xumm-for-woocommerce') ?></li>
    <li><?php _e('Signin using your Xumm App', 'xumm-for-woocommerce') ?></li>
    <li><?php _e('Set your Trustlines and go ahead!', 'xumm-for-woocommerce') ?></li>
</ol>

<?php endif ?>

<table class="form-table">
    <?php
        $context->generate_settings_html();
    ?>
</table>

<?php if (!empty($context->api) && !empty($context->api_secret)): ?>
<button type="button" class="create-payload" data-action="set_destination" id="set_destination" style="border-style: none; cursor:pointer; background-color: transparent;">
    <img src="<?php echo plugin_dir_url( __FILE__ ) .'/../../public/images/signin.svg'; ?>" width="220" style="padding:0" />
</button>
<?php endif; ?>

<?php if (!empty($context->logged_in)): ?>
<button type="button" class="button-primary create-payload" data-action="set_trustline" id="set_trustline" disabled="disabled">
    <?php echo __('Add Trustline', 'xumm-for-woocommerce'); ?>
</button>
<?php endif; ?>

<script>
    jQuery(function ()
    {
        jQuery(".create-payload").click(function (e)
        {
            e.preventDefault();

            const action = jQuery(this).data('action');
            xrpl_loader(true);

            let data = {
                'action': 'create_payload',
                'value': action
            };

            if (action == 'set_trustline')
            {
                data.woocommerce_xumm_currencies = jQuery('select[name="woocommerce_xumm_currencies"]').val();
                data.woocommerce_xumm_issuers = jQuery('select[name="woocommerce_xumm_issuers"]').val();
            }

            jQuery.ajax({
                url: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
                type: 'POST',
                data: data,
                success: function (response)
                {
                    xrpl_loader(false);

                    if (response.status == 'ok') {
                        window.location.href = response.redirect_url;
                    } else {
                        const message = `<div class="notice notice-error is-dismissible"><p>${response.message}</p></div>`;
                        jQuery('h1.screen-reader-text').after(message);
                        window.scrollTo(0, 0);
                    }
                }
            });
        });
    });
</script>

<?php include_once(dirname(__FILE__) . '/xumm-for-woocommerce-admin-loader.php'); ?>

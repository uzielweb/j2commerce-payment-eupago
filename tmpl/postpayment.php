<?php

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \stdClass $vars */
?>

<div class="j2commerce-payment-plugin j2commerce-payment-eupago card border-0 shadow-sm mb-4">
    <div class="card-body">
        
        <?php if (!empty($vars->onafterpayment_text)) : ?>
            <div class="alert alert-success mb-4">
                <?php echo $vars->onafterpayment_text; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($vars->eupago)): ?>
            <div class="eupago-payment-info" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border: 2px solid #4a90e2; border-radius: 12px; padding: 30px; margin: 20px auto; max-width: 500px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; box-shadow: 0 8px 25px rgba(0,0,0,0.1);">
                <div class="eupago-header" style="text-align: center; color: #2c3e50; font-size: 22px; font-weight: 600; margin-bottom: 25px; border-bottom: 2px solid #4a90e2; padding-bottom: 15px;">
                    <?php echo Text::_('PLG_J2COMMERCE_PAYMENT_EUPAGO_PAYMENT_INFO'); ?>
                </div>

                <div class="eupago-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 12px 0; border-bottom: 1px dotted #bdc3c7;">
                    <span class="eupago-label" style="font-weight: 600; color: #34495e; font-size: 16px;"><?php echo Text::_('PLG_J2COMMERCE_PAYMENT_EUPAGO_ENTITY'); ?>:</span>
                    <span class="eupago-value" style="font-weight: 700; color: #2c3e50; font-size: 16px;"><?php echo htmlspecialchars($vars->eupago['entity']); ?></span>
                </div>

                <div class="eupago-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 12px 0; border-bottom: 1px dotted #bdc3c7;">
                    <span class="eupago-label" style="font-weight: 600; color: #34495e; font-size: 16px;"><?php echo Text::_('PLG_J2COMMERCE_PAYMENT_EUPAGO_REFERENCE'); ?>:</span>
                    <span class="eupago-value eupago-reference" style="font-size: 20px !important; letter-spacing: 2px; color: #e74c3c !important; font-family: 'Courier New', monospace; font-weight: 700;"><?php echo htmlspecialchars($vars->eupago['reference']); ?></span>
                </div>

                <div class="eupago-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 12px 0; border-bottom: 1px dotted #bdc3c7;">
                    <span class="eupago-label" style="font-weight: 600; color: #34495e; font-size: 16px;"><?php echo Text::_('PLG_J2COMMERCE_PAYMENT_EUPAGO_AMOUNT'); ?>:</span>
                    <span class="eupago-value eupago-amount" style="font-size: 18px !important; color: #27ae60 !important; font-weight: 700;"><?php echo htmlspecialchars($vars->eupago['amount']); ?></span>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

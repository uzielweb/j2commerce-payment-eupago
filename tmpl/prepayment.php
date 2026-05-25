<?php

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var \stdClass $vars */
?>

<div class="j2commerce-payment-plugin j2commerce-payment-eupago card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <h4 class="card-title h5 mb-3"><?php echo htmlspecialchars($vars->display_name, ENT_QUOTES, 'UTF-8'); ?></h4>

        <?php if (!empty($vars->onbeforepayment_text)) : ?>
            <div class="j2commerce-payment-description alert alert-info bg-opacity-10 border-info border-opacity-25 mb-4">
                <?php echo $vars->onbeforepayment_text; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo Route::_('index.php?option=com_j2commerce&view=checkout&task=checkout.confirm&paction=process&order_id=' . $vars->order_id . '&orderpayment_type=' . $vars->orderpayment_type); ?>" method="post" name="adminForm" id="adminForm" class="j2commerce-payment-form">
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button type="submit" class="btn btn-primary btn-lg j2commerce-btn-submit">
                    <?php echo htmlspecialchars($vars->button_text, ENT_QUOTES, 'UTF-8'); ?>
                </button>
            </div>

            <input type="hidden" name="hash" value="<?php echo htmlspecialchars($vars->hash, ENT_QUOTES, 'UTF-8'); ?>" />
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
</div>

<?php

defined('_JEXEC') or die;

/** @var \stdClass $vars */
?>
<div class="j2commerce-payment-plugin j2commerce-payment-eupago alert alert-danger shadow-sm">
    <div class="d-flex align-items-center">
        <i class="fa fa-exclamation-triangle fa-2x me-3"></i>
        <div>
            <h5 class="alert-heading mb-1"><?php echo \Joomla\CMS\Language\Text::_('ERROR'); ?></h5>
            <p class="mb-0"><?php echo htmlspecialchars($vars->message, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    </div>
</div>

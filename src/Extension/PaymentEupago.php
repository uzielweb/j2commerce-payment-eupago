<?php

declare(strict_types=1);

namespace J2Commerce\Plugin\J2Commerce\PaymentEupago\Extension;

use J2Commerce\Component\J2commerce\Administrator\Helper\OrderHistoryHelper;
use J2Commerce\Component\J2commerce\Administrator\Library\Plugins\Base;
use J2Commerce\Component\J2commerce\Administrator\Library\Plugins\Payment;
use J2Commerce\Component\J2commerce\Administrator\Library\Plugins\PluginLayoutTrait;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

final class PaymentEupago extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use PluginLayoutTrait;

    protected $autoloadLanguage = true;
    protected $_element = 'payment_eupago';
    protected $_type = 'j2commerce';

    private ?Payment $payment = null;
    private ?Base $base = null;

    private DispatcherInterface $pluginDispatcher;
    private array $pluginConfig;

    public function __construct(
        DispatcherInterface $dispatcher,
        array $config,
        private Language $language,
        DatabaseInterface $db
    ) {
        parent::__construct($dispatcher, $config);

        $this->pluginDispatcher = $dispatcher;
        $this->pluginConfig     = $config;
        $this->setDatabase($db);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'onJ2CommerceGetPaymentPlugins' => 'onGetPaymentPlugins',
            'onJ2CommercePrePayment'        => 'onPrePayment',
            'onJ2CommercePostPayment'       => 'onPostPayment',
        ];
    }

    public function onGetPaymentPlugins(Event $event): void
    {
        $result   = $event->getArgument('result', []);
        $result[] = [
            'element' => $this->_element,
            'name'    => Text::_($this->params->get('display_name', 'PLG_J2COMMERCE_PAYMENT_EUPAGO_DISPLAY_NAME')),
            'image'   => '',
        ];
        $event->setArgument('result', $result);
    }

    public function onPrePayment(Event $event): void
    {
        $args    = $event->getArguments();
        $element = $args[0] ?? '';
        $data    = $args[1] ?? [];

        if ($element !== $this->_element) {
            return;
        }

        $result   = $event->getArgument('result', []);
        $result[] = $this->prePayment($data);
        $event->setArgument('result', $result);
    }

    public function onPostPayment(Event $event): void
    {
        $args    = $event->getArguments();
        $element = $args[0] ?? '';
        $data    = $args[1] ?? [];

        if ($element !== $this->_element) {
            return;
        }

        $result   = $event->getArgument('result', []);
        $result[] = $this->postPayment((object) $data);
        $event->setArgument('result', $result);
    }

    private function getPayment(): Payment
    {
        if ($this->payment === null) {
            $this->payment           = new Payment($this->pluginDispatcher, $this->pluginConfig);
            $this->payment->_element = $this->_name;
        }

        return $this->payment;
    }

    private function getBase(): Base
    {
        if ($this->base === null) {
            $this->base = new Base($this->pluginDispatcher, $this->pluginConfig);
        }

        return $this->base;
    }

    private function createOrderTable(): object
    {
        return Factory::getApplication()
            ->bootComponent('com_j2commerce')
            ->getMVCFactory()
            ->createTable('Order', 'Administrator');
    }

    private function prePayment(array $data): string
    {
        $this->ensureLanguageLoaded();

        $vars                      = new \stdClass();
        $vars->order_id            = $data['order_id'];
        $vars->orderpayment_id     = $data['orderpayment_id'];
        $vars->orderpayment_amount = $data['orderpayment_amount'];
        $vars->orderpayment_type   = $this->_element;

        $vars->display_name         = Text::_($this->params->get('display_name', 'PLG_J2COMMERCE_PAYMENT_EUPAGO'));
        $vars->onbeforepayment_text = $this->params->get('onbeforepayment', '');
        $vars->button_text          = Text::_('COM_J2COMMERCE_PLACE_ORDER');

        $order = $this->createOrderTable();
        $order->load(['order_id' => $vars->order_id]);

        $vars->hash = $this->getPayment()->generateHash($order);

        $layoutPath = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_element . '/tmpl';

        return (new FileLayout('prepayment', $layoutPath))->render(['vars' => $vars]);
    }

    private function postPayment(object $data): string
    {
        $this->ensureLanguageLoaded();

        $app     = Factory::getApplication();
        $vars    = new \stdClass();
        $paction = $app->getInput()->getString('paction');

        $layoutPath = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_element . '/tmpl';

        return match ($paction) {
            'display' => $this->postPaymentDisplay($vars, $layoutPath),
            'process' => $this->postPaymentProcess($data),
            default   => $this->postPaymentError($vars, $layoutPath),
        };
    }

    private function postPaymentDisplay(\stdClass $vars, string $layoutPath): string
    {
        $app = Factory::getApplication();
        $orderId = $app->getInput()->getString('order_id');
        $order = $this->createOrderTable();
        
        $vars->onafterpayment_text = $this->params->get('onafterpayment', '');
        $vars->eupago = null;

        if ($order->load(['order_id' => $orderId])) {
            $orderParams = json_decode($order->order_params ?? '{}', true) ?: [];
            if (!empty($orderParams['eupago_data'])) {
                $vars->eupago = $orderParams['eupago_data'];
            }
        }

        $html = (new FileLayout('postpayment', $layoutPath))->render(['vars' => $vars]);
        $html .= $this->getBase()->_displayArticle();

        return $html;
    }

    private function postPaymentProcess(object $data): string
    {
        if (!Session::checkToken()) {
            return json_encode(['error' => Text::_('JINVALID_TOKEN')]);
        }

        return json_encode($this->processPayment());
    }

    private function postPaymentError(\stdClass $vars, string $layoutPath): string
    {
        $vars->message = $this->params->get('onerrorpayment', Text::_('PLG_J2COMMERCE_PAYMENT_EUPAGO_ERROR_GENERATING'));

        return (new FileLayout('message', $layoutPath))->render(['vars' => $vars]);
    }

    private function processPayment(): array
    {
        $app     = Factory::getApplication();
        $orderId = $app->getInput()->getString('order_id');
        $json    = [];

        $order = $this->createOrderTable();

        if (!$order->load(['order_id' => $orderId])) {
            $json['error'] = Text::_('PLG_J2COMMERCE_PAYMENT_EUPAGO_ERROR_GENERATING');
            return $json;
        }

        if ($order->orderpayment_type !== $this->_element || !$this->getPayment()->validateHash($order)) {
            $json['error'] = Text::_('PLG_J2COMMERCE_PAYMENT_EUPAGO_ERROR_GENERATING');
            return $json;
        }

        $apiKey  = trim($this->params->get('api_key', ''));
        $entity  = trim($this->params->get('entity', '11249'));
        $sandbox = (bool) $this->params->get('sandbox', 0);
        $amount  = round((float)$order->order_subtotal + (float)$order->order_shipping + (float)$order->order_shipping_tax, 2);

        $url = $sandbox ? 'https://sandbox.eupago.pt/clientes/rest_api/multibanco/create' : 'https://clientes.eupago.pt/clientes/rest_api/multibanco/create';

        $postData = [
            'chave'  => $apiKey,
            'valor'  => $amount,
            'id'     => $order->order_id,
            'entity' => $entity
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            Log::add('EuPago API Error: HTTP ' . $httpCode . ' Response: ' . $response, Log::ERROR, 'com_j2commerce');
            $json['error'] = Text::_('PLG_J2COMMERCE_PAYMENT_EUPAGO_ERROR_GENERATING');
            return $json;
        }

        $decoded = json_decode($response, true);
        if (!isset($decoded['sucesso']) || !$decoded['sucesso'] || !isset($decoded['referencia'])) {
            Log::add('EuPago API Error (Invalid Data): ' . $response, Log::ERROR, 'com_j2commerce');
            $json['error'] = Text::_('PLG_J2COMMERCE_PAYMENT_EUPAGO_ERROR_GENERATING');
            return $json;
        }

        $reference = $decoded['referencia'];
        $formattedRef = chunk_split($reference, 3, ' ');
        $formattedAmount = number_format($amount, 2, ',', '.') . ' €';
        
        $eupagoData = [
            'entity'    => $entity,
            'reference' => $formattedRef,
            'amount'    => $formattedAmount,
        ];

        // Format note for backend / email
        $html = '<div style="background:#f5f7fa; padding:15px; border-radius:5px; border:1px solid #ddd; max-width:300px;">';
        $html .= '<strong>' . Text::_('PLG_J2COMMERCE_PAYMENT_EUPAGO_PAYMENT_INFO') . '</strong><br><br>';
        $html .= '<strong>' . Text::_('PLG_J2COMMERCE_PAYMENT_EUPAGO_ENTITY') . ':</strong> ' . $entity . '<br>';
        $html .= '<strong>' . Text::_('PLG_J2COMMERCE_PAYMENT_EUPAGO_REFERENCE') . ':</strong> ' . $formattedRef . '<br>';
        $html .= '<strong>' . Text::_('PLG_J2COMMERCE_PAYMENT_EUPAGO_AMOUNT') . ':</strong> ' . $formattedAmount . '<br>';
        $html .= '</div>';

        $array = json_decode($order->order_params ?? '{}', true) ?: [];
        $array['eupago_data'] = $eupagoData;
        $order->order_params = json_encode($array);
        $order->customer_note = ($order->customer_note ?? '') . '<br>' . $html;

        $orderStateId = (int) $this->params->get('payment_status', 4);
        $order->order_state_id = $orderStateId;

        if (!$order->store()) {
            Log::add('Eupago order save failed: ' . $order->getError(), Log::ERROR, 'com_j2commerce');
            $json['error'] = Text::_('PLG_J2COMMERCE_PAYMENT_EUPAGO_ERROR_GENERATING');
            return $json;
        }

        OrderHistoryHelper::add(
            orderId: $order->order_id,
            comment: 'EuPago Multibanco Reference Generated: ' . $reference,
            orderStateId: $orderStateId,
            customerNotified: 1
        );

        $json['success']  = '';
        $json['redirect'] = $this->getPayment()->getReturnUrl();

        return $json;
    }

    private function ensureLanguageLoaded(): void
    {
        static $loaded = false;

        if (!$loaded) {
            $this->language->load('com_j2commerce', JPATH_ADMINISTRATOR);
            $this->language->load('plg_j2commerce_payment_eupago', JPATH_ADMINISTRATOR);
            $loaded = true;
        }
    }
}

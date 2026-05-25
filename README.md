# J2Commerce Payment - EuPago Multibanco

Este é o plugin oficial de integração do **EuPago Multibanco** para o componente de e-commerce **J2Commerce** (Joomla 5/6).

## Recursos

- Geração automática de Referência e Entidade Multibanco através da API do EuPago.
- Suporte ao Modo Sandbox para testes.
- Personalização de status do pedido após a geração da referência.
- Frontend responsivo e integrado nativamente com os templates do J2Commerce.

## Instalação

1. Baixe o arquivo `.zip` da versão mais recente na página de [Releases](https://github.com/uzielweb/j2commerce-payment-eupago/releases).
2. No painel administrativo do Joomla, vá em **Sistema** > **Instalar** > **Extensões**.
3. Faça o upload do arquivo `.zip`.
4. Vá em **Sistema** > **Plugins**, pesquise por `eupago` e ative o plugin `Payment - EuPago Multibanco`.

## Configuração

Acesse as opções do plugin e preencha as seguintes informações:

- **Chave da API:** Sua API Key (obtida no backoffice da EuPago).
- **Entidade:** A entidade fornecida pela EuPago (ex: 11249 ou 11604).
- **Modo Sandbox:** Sim/Não (para desenvolvimento).
- **Status do Pedido:** O status que o pedido receberá após a referência ser gerada.

## Suporte

Se você encontrar algum bug ou quiser sugerir uma melhoria, por favor abra uma [Issue](https://github.com/uzielweb/j2commerce-payment-eupago/issues).

---

**Licença:** GNU/GPLv2 or later.

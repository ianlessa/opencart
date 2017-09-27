# language: pt
Funcionalidade: Instalação do módulo MundiPagg
    Eu como usuário
    Desejo instalar o módulo de pagamento da MundiPagg
    Para conseguir transacionar pagamentos pela MundiPagg

    Cenário de Fundo: Login no admin
        Quando vou para "/admin"
        E preencho "Username" com "admin"
        E preencho "Password" com "admin"
        E pressiono "Login"

	@javascript
	Esquema do Cenário: Criação de custom field Número
        Quando clico no elemento "#menu-customer"
        E clico no elemento "a[href*='customer/custom_field']"
        E clico no elemento "a[href*='custom_field/add']"
        E preencho "Custom Field Name" com "<name>"
        E seleciono "Address" de "input-location"
        E seleciono "Text" de "input-type"
        E marco "custom_field_customer_group[0][customer_group_id]"
        E marco "custom_field_customer_group[0][required]"
        E seleciono "Enabled" de "input-status"
        E clico no elemento "div.pull-right > button:nth-child(1)"

        Exemplos:
            | name        |
            | Número      |
            | Complemento |

    @javascript
    Cenário: Configurações gerais
        Quando clico no elemento "#menu-extension"
        E clico no elemento "a[href*='marketplace/extension']"
        E espero o texto "Analytics Name" aparecer
        E seleciono "Payments" de "type"
        E espero o texto "MundiPagg" aparecer
        E clico no elemento "a[href*='extension=mundipagg']"
        E espero o elemento "a[href*='extension/payment/uninstall'][href*='mundipagg']" aparecer
        E clico no elemento "a[href*='extension/payment/mundipagg']"
        E devo ver "Edit Mundipagg payments"
        E clico no elemento "#module-enabled"
        E preencho "Payment title" com "MundiPagg"
        E seleciono "Número" de "Number"
        E seleciono "Complemento" de "Complement"
        E preencho a "secret" key de "prod"
        E preencho a "secret" key de "test"
        E preencho a "public" key de "prod"
        E preencho a "public" key de "test"
        E clico no elemento "#test-mode-enabled"
        E clico no elemento "#log-enabled"
        E sigo o link "Credit card"
        E clico no elemento "#credit-card-enabled"
        E preencho "credit-card-title" com "Cartão de crédito"
        E preencho "Invoice's name" com "Loja OpenCart - CC"
        E sigo o link "Boleto"
        E preencho "boleto-title" com "Boleto"
        E preencho "Name" com "Loja OpenCart - Boleto"
        E preencho "payment_mundipagg_boleto_instructions" com "Pague em dia!!!"
        E pressiono "submit-form"
        Então espero o texto "Mundipagg options saved!" aparecer
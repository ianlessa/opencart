# language: pt
Funcionalidade: Instalação do módulo MundiPagg
    Eu como usuário
    Desejo instalar o módulo de pagamento da MundiPagg
    Para conseguir transacionar pagamentos pela MundiPagg

    @javascript
    Cenário: Instalação do módulo MundiPagg
        Quando vou para "/admin"
        E preencho "Username" com "admin"
        E preencho "Password" com "admin"
        E pressiono "Login"
        E clico no elemento "#menu-extension"
        E clico no elemento "a[href*='marketplace/extension']"
        E espero o texto "Analytics Name" aparecer
        E seleciono "Payments" de "type"
        E espero o texto "MundiPagg" aparecer
        E clico no elemento "a[href*='extension=mundipagg']"
        E espero o elemento "a[href*='extension/payment/uninstall'][href*='mundipagg']" aparecer
        E clico no elemento "a[href*='extension/payment/mundipagg']"
        E devo ver "Edit Mundipagg payments"
        E clico no elemento "#module-enabled"
        E preencho a "secret" key de "prod"
        E preencho a "secret" key de "test"
        E preencho a "public" key de "prod"
        E preencho a "public" key de "test"
        E clico no elemento "#log-enabled"
        E pressiono "submit-form"
        Então espero o texto "Mundipagg options saved!" aparecer

# language: pt
Funcionalidade: Instalação do módulo MundiPagg
    Eu como usuário
    Desejo instalar o módulo de pagamento da MundiPagg
    Para conseguir transacionar pagamentos pela MundiPagg

    Contexto: Instalação do OpenCart
        Então instalo o OpenCart

    @javascript
    Cenário: Instalação do módulo MundiPagg
        Quando vou para "http://localhost:8080/admin"
        E preencho "Username" com "admin"
        E preencho "Password" com "admin"
        E pressiono "Login"
        E clico no elemento "#button-menu"
        E espero o texto "Extensions" aparecer
        E clico no elemento "#menu-extension"
        E sigo o link "Extensions"
        E espero o texto "Status" aparecer
        E seleciono "Payments" de "type"
        E espero o texto "MundiPagg" aparecer
        E clico no elemento "a[href*='extension=mundipagg']"
        E espero o elemento "a[href*='extension/payment/uninstall'][href*='mundipagg']" aparecer
        E clico no elemento "a[href*='extension/payment/mundipagg']"
        E devo ver "Edit Mundipagg payments"
        E clico no elemento "#module-enabled"
        E preencho a secret key de produção
        E clico no elemento "#log-enabled"
        E pressiono "submit-form"
        Então espero o texto "Mundipagg options saved!" aparecer

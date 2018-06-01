<?php
namespace Mundipagg\Helper;

class AdminMenu
{
    private $openCart;

    public function __construct($openCart)
    {
        $this->openCart = $openCart;
    }

    public function getMenu()
    {
        $htmlLogo =
            $this
                ->openCart
                ->load
                ->view('extension/payment/mundipagg/menu/mundipagg');

        $children[] = $this->getMenuChildren('Settings');
        $children[] = $this->getMenuChildren('Subscriptions');
        $children[] = $this->getMenuChildren('Plans');

        $mundipaggMenu = [
            'id'       => 'menu-mundipagg',
            'name'	   => $htmlLogo,
            'children' => $children
        ];

        return $mundipaggMenu;
    }

    private function getMenuChildren($name)
    {
        $path = 'extension/payment/mundipagg/' . strtolower($name);

        return [
            'name'  => $name,
            'href'  => $this->getLink($path)
        ];
    }

    private function getLink($path)
    {
        return $this->openCart->url->link(
            $path,
            'user_token=' . $this->openCart->session->data['user_token'],
            true
        );
    }
}
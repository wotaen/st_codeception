<?php


class CartSumCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }

    public function testSumInCart(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->moveMouseOver('#products > div:nth-child(5)');
        $I->click('Do košíku', '#products > div:nth-child(5)');
        $I->waitForElementVisible('#colorbox', 10);
        $I->click('#cboxClose'); //hide the popup
        $I->waitForElementNotVisible('#colorbox', 10);
        $I->moveMouseOver('#products > div:nth-child(4)');
        $I->click('Do košíku', '#products > div:nth-child(4)');
        $I->waitForElementVisible('#colorbox', 10);
        $I->click('#cboxClose'); //hide the popup
        $I->waitForElementNotVisible('#colorbox', 10);
        $I->see('1 641 Kč', '.cart-price');
        $I->amOnPage('/kosik');
        $I->see('1 641 Kč', '.price.price-primary');
        $I->see('1 356,20 Kč', '.price.price-secondary');
    }
}

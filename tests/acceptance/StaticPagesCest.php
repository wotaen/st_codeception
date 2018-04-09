<?php


class StaticPagesCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }

    /**
     * @example(url="/", title="Vítejte v našem testovacím e-shopu - Beta.shoptet.cz")
     * @example(url="/obchodni-podminky/", title="Obchodní podmínky - Beta.shoptet.cz")
     * @example(url="/napiste-nam/", title="Napište nám - Beta.shoptet.cz")
     * @example(url="/detske-obleceni/", title="Dětské oblečení - Beta.shoptet.cz")
     * @example(url="/detske-obleceni/columbia-squish-n--stuff-2/", title="Columbia Squish N’ Stuff - Beta.shoptet.cz")
     */
    public function staticPages(AcceptanceTester $I, \Codeception\Example $example)
    {
        $I->amOnPage($example['url']);
        $I->seeInTitle($example['title']);
    }
}

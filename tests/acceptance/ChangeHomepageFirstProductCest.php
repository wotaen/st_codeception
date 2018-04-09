<?php


class ChangeHomepageFirstProductCest
{
    public function _before(AcceptanceTester $I)
    {
        $mysqli = new mysqli("127.0.0.1", "root", "root", 'st_9993', "3307");
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
        $pwd = '$2a$12$vbndoH9gL09k0.mKc8YlKumT85bEqzRR6x11CzFJXfVFUCjDHA9EW';
        $mysqli->query("update admin_users set `password`='$pwd' where email='jiraskova@shoptet.cz'");
        $mysqli->close();
    }

    public function _after(AcceptanceTester $I)
    {
    }


    public function test(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->seeNoDifferenceToReferenceImage('Satchel Product', 'div.product-3-layout:nth-child(1)');

        //login to admin
        $I->amOnPage('/admin/login/');
        $I->fillField('email', 'jiraskova@shoptet.cz');
        $I->fillField('password', 'blablablah');
        $I->click('Přihlášení');

        $I->amOnPage('/admin/homepage/1/');
        $I->seeNoDifferenceToReferenceImage('First Row with Satchel Product','.std-table-listing tbody tr:first-child');
        $I->dragAndDrop('.std-table-listing tbody tr:first-child .move-item', '.std-table-listing tbody tr:nth-child(3) td:last-child');
        $I->seeNoDifferenceToReferenceImage('First Row with Nike Product','.std-table-listing tbody tr:first-child');
        $I->click('Uložit');

        $I->amOnPage('/');
        $I->seeNoDifferenceToReferenceImage('Nike Product', 'div.product-3-layout:nth-child(1)');

    }
}

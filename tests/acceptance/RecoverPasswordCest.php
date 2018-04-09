<?php

class RecoverPasswordCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
        $mbox = imap_open("{localhost:8143}INBOX", "catchall", "catchall")
        or die("Can't connect: " . imap_last_error());

        $check = imap_mailboxmsginfo($mbox);
        echo "Messages before delete: " . $check->Nmsgs . "\n";

        imap_delete($mbox, 1);

        $check = imap_mailboxmsginfo($mbox);
        echo "Messages after  delete: " . $check->Nmsgs . "\n";

        imap_expunge($mbox);

        $check = imap_mailboxmsginfo($mbox);
        echo "Messages after expunge: " . $check->Nmsgs . "\n";

        imap_close($mbox);
    }

    public function lostPasswordSendsEmail(AcceptanceTester $I)
    {
        $newPassword = 'blablablah';

        //password reset
        $I->amOnPage('/admin/login/');
        $I->seeNoDifferenceToReferenceImage('Lost password link', 'div.forgot-pass a');
        $I->click('div.forgot-pass > a');
        $I->fillField('email', 'jiraskova@shoptet.cz');
        $I->click('Odeslat');
        $I->see('Email byl úspěšně odeslán.');

        //extract reset link from email
        $I->canSeeEmail('SUBJECT "Beta.shoptet.cz přístup do administrace"');
        $I->openEmail('SUBJECT "Beta.shoptet.cz přístup do administrace"');
        preg_match_all(
            '/^(http:\/\/[a-z]+\.shoptet\.cz.*)$/m',
            $I->grabEmail()->textPlain,
            $resetPasswordLinkTokens);
        $resetPasswordLink = $resetPasswordLinkTokens[0][0];

        //set new password
        $I->amOnUrl($resetPasswordLink);
        $I->see('Vložte vaše nové heslo.');
        $I->see('Nastavení účtu');
        $I->fillField('newPassword', $newPassword);
        $I->fillField('newPasswordAgain', $newPassword);
        $I->click('Uložit');
        $I->canSee('Heslo bylo úspěšně změněno.');

        //logout
        $I->amOnPage('/admin/logout/');
        $I->seeCurrentUrlEquals('/admin/login/');

        //try login with new password
        $I->fillField('email', 'jiraskova@shoptet.cz');
        $I->fillField('password', $newPassword);
        $I->click('Přihlášení');
        $I->canSee('Základní přehled');
        $I->canSee('Dnešní prodeje');
    }
}

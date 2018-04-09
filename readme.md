
Codeception proof-of-concept 
===

Krátké představení
---

Codeception je knihovna na testování, zná 3 druhy testů:
1) Acceptance - to jsou end2end s použitím headless browseru, tj. bude fungovat JS, screenshoty
2) Functional - testování pomocí PhpDriver, nebude fungovat JS, je podstatně rychlejší než 1, ale nepouští JS
3) Unit - unit testy, tady lze použít snapshoty

Na automatizaci práci s prohlížečem používám Selenium. Ještě existuje chromedriver, ale ten neumí některé věci. Selenium
umí jak Chrome, tak i FF, existuje i IE driver (https://github.com/SeleniumHQ/selenium/wiki/InternetExplorerDriver), vím že funguje
a měl by jít taky nastavit

Nastavení
---

- spustit selenium: `java -jar chromedriver/selenium-3.11.0`
- ve vagrantu si udělat čisté prostředí pro 9993: `vg-setup-project 9993`
- nainstalovat závislosti: `composer install`
- vyprázdnit catchall imap schránku ve vagrantu
- pustit nějaký test, viz. dále

Workflow pro reset hesla
---

`vendor/bin/codecept run --steps acceptance RecoverPasswordCest`

Zde proběhne celé kolečko resetu hesla, přečtení emailu, nastavení nového hesla a test
nového hesla.

Důležitá část zde je metoda `_after` která smaže nově příchozí email a uvede tak schránku do 
původního stavu.


Workflow pro změnu prvního produktu na homepage
---

Před puštěním tohoto testu je potřeba smazat databázi `st_9993` a ve vagrantu potom `vg-setup-project 9993`

`vendor/bin/codecept run --steps acceptance ChangeHomepageFirstProductCest`

Tento test používá screenshoty na porovnávání, jsou uložené v `tests/_data/referenceImages/1920x1200`. Pokud
rozdíl obrázků překročí určitou mez, tak test failne a v `tests/_output/failimages` jsou potom k nahlédnutí změny. 
Pokud je změna v pořádku, potom stačí referenční image smazat a test provést znovu a obrázek se přegeneruje.

Zajímavé na tomto testu je také použití drag/drop na změnu úvodního produktu

Ostatní acceptance testy
---

- `CartSumCest.php` - zkontroluje košík po přidání 2 produktů - použití `waitFor...`
- `StaticPagesCest.php` - použití dataloaderů pro opakované testy


Věci na zamyšlení
---

**Kdy pouštět testy**

Mějme vzorový task - potřebujeme posunout tlačítko o 50px doprava:
- developer posune tlačítko a zkontroluje u sebe v prohlížeči, NEPOUŠTÍ test
- tester pustí test, vidí, že test failuje, podívá se proč a pokud se mu líbí nová pozice tlačítka
tak původní ref. image smaže a vygeneruje nový
- developer nemůže smazat ref. image sám - tester se nemá jak dozvědět, že test failnul

Jiný vzorový task - úprava workflow:
- developer upraví workflow, upraví i seleniové testy, a může si pouštět testy u sebe
- testy budou pouštět i tester

**v čem pouštět selenium server**

Obrázky prohlížeče generují trochu jinak ve FF, jinak v chromu, jinak pod windows, linuxem a macos.
Bude potřeba zaručit jednotné prostředí testerům, tj. stejné verze chromu, firefoxu a OS. To může být problém.
Dá se to řešit vhodným docker image, viz. `https://github.com/SeleniumHQ/docker-selenium` - testy se budou pouštět vždy proti standardizovanému seleniu/prohlížeči.

**reset stavu po testu**

Asi hlavní bod a problém celého testování.

Testy musí vždy běžet izolovaně, tj v metodách `_before` a `_after` inicializovat stav a potom ho 
případně zrušit. Jeden test nemůže být závislý na druhém. To je problém u mého příkladu `ChangeHomepageFirstProductCest`
tam dojde ke změně prvního produktu a pokud se test pustí znovu, bude failovat (proto poznámka o tom, že se musí provést `vg-setup-project 9993`)

Šlo by vrátit vše zpět přímo pomocí selenia, ale to není optimální, dá se použít anotací `@before` a `@after`. Cílem je mít vždy
databázi v původním stavu, resp. ve stavu, ve kterém je potřeba.

V rámci této rešerže jsem zkoušel různé postupy:
1) dump a restore databáze pres `mysqldump` - dost pomalé, tj. nevhodné.
2) automatické `vg-setup-project 9993`, taky hodně pomalé

Spolehlivé a rychlé řešení bude použít docker. Jsou v zásadě 2 možnosti:
1) Vytvořit kompletně izolované prostředí celé aplikace v Dockeru.
2) Vytvořit image jenom na databázi. Oproti předchozímu bodu bude tohle o dost rychlejší na implementaci, ale bude
to chtít změnu v aplikaci - aby uměla použít databázi na jiném portu, tj. bude potřeba úprava konfigurace
nějak takto: "if user agent is headless selenium browser, then use db port xxxx"

Po spuštění bude kontejner vždy ve výchozím stavu. Tj. jakákoliv změna v DB bude viditelná jenom po dobu, co kontejner běží. 
Po zastavení běhu kontejneru se změny zahodí.

Asi bude možné uvést db do původního stavu vždy po testu (nějak) nebo v rámci testu (seleniovými kroky), ale
obojí bude "brittle" a podle mě přispívat obecně k nechuti testovat.

**co pokrýt testy?**

Testování Seleniem trvá dlouho, je dobré je pokrývat věci, které se moc nepoužívají a hrozí, že nebudou fungovat delší dobu bez povšimnutí

**kdo vytváří testy**

- developer při vývoji nové feature
- tester při testování existujícího systému
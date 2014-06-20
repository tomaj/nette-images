Nette Image
===========

Specialna kniznica pre lahsiu pracu s obrazkami. Umoznuje ukladat obrazky do roznych storagov a roznej struktury.
Rovnako umoznuje vytvarat priamo k obrazkom rozne thumby.

Instalacia
----------

Najlepsie cez composer
```
doplnit kod ked bude libka v composeri
```

Nastavenie
----------

Pre fungovanie je potrebne zaregistrovat a nastavit, pdola toho aky backend chceme pouzivat.
Backend je treba zaregistraovat ako nette service. Pouzitie klasickeho file backendu: ```fileBackend: Tomaj\Image\Backend\FileBackend('uploads/images/')```
alebo je mozne pouzit S3 backend ```s3Backend: Tomaj\Image\Backend\S3Backend('replace-acces-key-id', 'replace-secret-key-id', 'bucket-name', 'eu-west-1', 'bucketname.s3-eu-west-1.amazonaws.com')```.
Nastavenie je treba upravit podla toho kam na S3 chceme obrazok ukladat

Celkova konfiguracia moze vyzerat napriklad takto:
```
fileBackend: Tomaj\Image\Backend\FileBackend('uploads/')
userImage: Tomaj\Image\ImageService(@fileBackend, 'avatar/:year/:month/:day/:hash', '/tmp', ['10x10', '320x200', '640x360'])
```

Aplikacia moze obsahovat viacero instancii sluzby *ImageService* kde kazda moze pouzivat iny backend a ine velkosti pre obrazky

Pre fungovanie helpra v sablonach ho je potrebne zaregistrovat v presenteri:

```
$context = $this->context;
$this->template->registerHelper('thumb', function($identifier, $type, $size = '') use ($context) {
	$imageService = $context->getService($type . 'Image');
	return \Tomaj\Image\Helper\Image::thumb($imageService, $identifier, $size);
});
```

Pouzitie
========

Pouzitie je rozdelene 2 casti - pridanie obrazku a vytvorenie linky na obrazok.

Upload
------

Novy obrazok je mozne pridat v kode cez nasledovanu funkciu:
```
$itemImage = $this->context->getService('itemImage')
$identifier = $itemImage->store('cesta k obrazku', 'meno vysledneho obrazku');
```
Tento *$identifier* ktory vrati metoda store() si treba ulozit pre spetne ziskanie obrazku

Novy obrazok je mozne pridat priamo z nette formulara takto:
```
TODO
```

Generovanie nahladov
--------------------

Pre vytvorenie nahladu je potrebna hodnota ktoru *ImageService* vratil po ulozenie obrazku.
Priklad moze vyzerat nasledovne:
```
$itemImage = $this->context->getService('itemImage')
$thumbUrl = $itemImage->url($identifier, '320x200');
// alebo pre povodny obrazok
$orginalUrl = $itemImage->url($identifier);
```
**NOTE:** v pripade ze zmensenina ktora sa da vytvorit neexistuje tak vytvori linka ktora bude odkazovat na neexistujuci zdroj, pokial tuto chybu neostruje priamo backend.

Tiez je mozne pouzit priamo vygenerovanie nahladu v sablone pomocou helpera (je potrebne ho zaregistrovat ako je uvedene na zaciatku)

```
<img src="{$identifier|thumb:'item','320x200'}" alt="moj zmenseny obrazok" />
```

Pri tomto pouziti je dolezite uvies druhy paramter(retazec) podla ktoreho sa pouzije spravny *ImageService* - v projekte ich moze byt viac.
Pomocou dependecy injection sa vytvori nazov sluzby ktora sa bude pouzivat - toto chovanie je mozne v aplikacii zmenit, nie je sucastou kniznice(implementacia je hore pri registrovanie helpera)


**Rozne typy nahladov**
Pre nahlady je mozne pouzit rozne presety podla ktorych sa budu obrazky pouzivat.
Konfiguracia potom vyzera takto:
```
userImage: Tomaj\Image\ImageService(@fileBackend, 'avatar/:year/:month/:day/:hash', '/tmp', ['10x10_FILL', '320x200_EXACT', '640x360_SHRINK_ONLY'])
```
Nazvy a funkcnost presne kopiruje nette dokumentaciu ku obrazkom. [http://doc.nette.org/cs/2.1/images](http://doc.nette.org/cs/2.1/images)
Defaultne je pouzity preset **EXACT**.

Vygenerovanie novej velkosti alebo pregenerovanie nahladov
----------------------------------------------------------

Na vygenerovanie novej velkosti existuje funkcia *regenerateThumb* v image servise. Staci ju zavolat nad identifiermi ktore mame a vygeneruju sa nove thumby.
Pouzitie moze vyzerat nasledovne:
```
$promoImageService = $container->getService('promoImage');
foreach ($container->getService('promoRepository')->findAll() as $promo) {
	$promoImageService->regenerateThumb($promo->image, '20x20');
}
```

Rozisrenie - pridanie noveho backendu
-------------------------------------

Pre pridanie dalsieho backendu staci implementovat interface *Tomaj\Image\Backend\BackendInterface*.
Vsetky potrebne info je mozne vycitat z komentarov priamo v tomto interfaci a implemtnacii *FileBackendu* a *S3Backendu*


TODO - co by bolo dobre este doriesit
=====================================

1. Aktualne sa vsety thumby generuju napevno ako **.jpg**
2. Treba doplnit funkcie pre mazanie obrazkov z backendu a upravu obrazku v backende
3. Pri generovani thumbov sa nasstavuje ImageQuality ktora je teraz napevno *80* v *ImageService*
4. Treba testnut mazanie temporarnych suborov/foldrov
5. Treba testnut ako sa sprava service ked dostane obrazky s diakritikou
6. Treba testnut ako sa sprava ked dostane velke obrazky
7. Treba testnut ako sa sprava ked dostane nevalidne obrazky z ktorych sa neda spravit thumb
8. Treba testnut ako sa sprava ked dostane v mene suboru znak '_'



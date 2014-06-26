<?php

namespace Tomaj\Image\Backend;

/**
 * Interface BackendInterface
 *
 * Zakladny interface pre backendy v image service
 * Pre kazdy backend je potrebne implementovat 4 funkcie vid nizsue aj s popismi
 *
 * @package Tomaj\Image\Backend
 */
interface BackendInterface
{
    /**
     * Zakladna funkcia pre ulozenie
     *
     * Uklada lokalny subor z $sourceFile do backendu. Povodne meno suboru sa posiela ako $originalName.
     * Source file moze mat lubovolny nazov (kvli uplodom a ich nazvom v tmp)
     * Kazdy subor je mozne ulozit v backende do nejakej cesty ktora sa posiela ako $path. Moze to byt napriklad data/2014/23/hocico
     * Pre vytvorenie zmensenin sa posiela pole $thumb ktore obsahuje hodnoty ako array('10x10_FILL', '128x100')
     *
     * Funkcia vrati identifikator pre obrazky pomocou ktoreho je mozne ziskat naspet urlku, resp. pracovat dalej s obrazkom cez backend.
     *
     * Tato operacia nemusi byt bezpecna, v pripade chyby moze vyhodit exception podla implementacie.
     *
     * @param $sourceFile
     * @param $originalName
     * @param $path
     * @param $thumbs
     * @return string
     */
    public function save($sourceFile, $originalName, $path, $thumbs = array());

    /**
     * Funkcia pre ziskanie urlky pre odservovanie obrazku.
     * Pre vytvorenie urlky je potrebny identifikator obrazku ktory sa vrati z funkcie save()
     * Parameter $thumb moze obsahovat nejaky preset z pola $thumbs z funkcie save()
     *
     * Tato operacia je bezpecna, nerobia ziadne operacie a nemala by vyhodit ziadnu exception.
     *
     * @param $identifier
     * @param null $thumb
     * @return string
     */
    public function url($identifier, $thumb = null);

    /**
     * Vracia cestu k lokalnemu suboru.
     * Vyuziva sa v pripade kde je storage na uplne inom disku alebo v cloude a je potrebne z neho spravit
     * znovu nejaku zmenseninu. Vtedy ho je potrebne stiahnut naspet lokalne a vratit tuto cestu.
     *
     * O nasledne mazanie sa je nutne postarat mimo kniznice.
     * Tato operacia moze vyhodit pri problemoch chybu ak nieco nie je splnene.
     *
     * @param $identifier
     * @return string
     */
    public function localFile($identifier);

    /**
     * Ulozi novy thumb (cest ak nemu je v $thumbPath) ku obrazku idenfikovaneho cez $identifier
     *
     * Tato operacia moze vyhodit vynimku podla toho ako je implementovana v backende.
     *
     * @param $thumbPath
     * @param $identifier
     * @param $thumb
     * @return string
     */
    public function saveThumb($thumbPath, $identifier, $thumb);
}

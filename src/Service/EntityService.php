<?php
/*
 * Created on Mon Nov 08 2021 by webmestre@cadot.info
 * Licence The MIT License (MIT) Copyright (c) 2021 Cadot.info,licence: http://spdx.org/licenses/MIT.html
 *
 *-------------------------------------------------------------------------- *
 *      EntityService.php *
 * -------------------------------------------------------------------------- *
 *
 * Usage:
 * - reorder (repository, data, limit, offset)              reorder a array by findall or by data gived
 * - getAllOfFields($repository, $field, $removeDoublon)    rerurn fields of Repository in string with/without doublons
 * - sortArrayObjetByArray($objets, $function, $array)      reorder array of objetcs by array
 *
 * Source on:many website, i can add copyrigth, mail me, thanks
 */

namespace Cadotinfo\EntityBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

class EntityService
{

    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    /**
     * reorder a array and return a array of repository by findall or datas
     *
     * @param  string $repository
     * @param  mixed $data  array at reorder
     * @param  int $limit
     * @param  int $offset
     * @return array
     */
    public function reorder(string $repository, $data = null, int $limit = 0, int $offset = 0): array
    {
        $tab = [];
        //si on a pas données un array on va chercher toutes les données du repository
        if (is_null($data))
            $data = $this->em->getRepository("App:" . ucfirst($repository))->findall();
        if ($data) {
            //on récupère le trie enregistré dans la bd
            if ($base = $this->em->getRepository("App:CM\Sortable")->findOneBy(['entite' => ucfirst($repository)])) {
                $tab = $this->sortArrayObjetByArray($data, 'getId', $base->getordre());
            } else {
                $tab = $data;
            }
        } else return [];
        $retour = $limit ? array_slice($tab, $offset, $limit) : array_slice($tab, $offset);
        return $retour;
    }

    //funtion qui récupère toutes les données d'un field

    /**
     * get All Of Field and remove doublon in option
     *
     * @param  string $repository
     * @param  string $field
     * @param  bool $removeDoublon
     * @return string
     */
    public function getAllOfFields(string $repository, string $field, bool $removeDoublon = true): string
    {
        $tabres = [];
        foreach ($this->em->getRepository("App:" . ucfirst($repository))->findall() as $entitie) {
            $methode = 'get' . ucfirst($field);
            $tab = explode(",", $entitie->$methode());
            $tabres = array_merge($tabres, $tab);
        }
        if ($removeDoublon) return implode(',', array_unique($tabres));
        else return implode(',', $tabres);
    }

    /**
     * Method sortArrayObjetByArray order a array of objects by another array or string 
     *
     * @param $objets array of objects
     * @param $function  getId, getName...
     * @param $array  array or string with the good range
     *
     * @return void
     */

    public function sortArrayObjetByArray($objets, $function, $array)
    {
        $reste = $objets;
        if (\is_string($array)) {
            $array = explode(',', $array);
        }
        $tab = [];
        foreach ($array as $num) {
            foreach ($objets as $key => $value) {
                if ($value->$function() == intval($num)) {
                    $tab[] = $objets[$key];
                    unset($reste[$key]);
                }
            }
        }
        return array_merge($tab, $reste);
    }
}

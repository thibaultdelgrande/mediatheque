<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Functions\Api;

class SearchController extends AbstractController
{
    #[Route('/recherche', name: 'app_search')]
    public function index(Api $api): Response
    {

        // Récupérer les données du formulaire
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
            $limit = $_GET['limit'] ?? 1;
            $page = $_GET['page'] ?? 1;
            $type = $_GET['type'] ?? 'all';
            
            
            // Faire une requête sur l'api de la bnf
            switch ($type){
                default:
                    $cat = "(bib.doctype%20adj%20%22a%22)%20or%20(bib.doctype%20adj%20%22h%22)";
                    break;
                case 'livre':
                    $cat = "bib.doctype%20adj%20%22a%22";
                    break;
                case 'video':
                    $cat = "bib.doctype%20adj%20%22h%22";
                    break;
            }
            $url = "https://catalogue.bnf.fr/api/SRU?version=1.2&operation=searchRetrieve&query=(bib.anywhere%20all%20%22".urlencode($search)."%22)%20and%20(".$cat.")%20not%20(bib.recordtype%20adj%20%22ens%22)&recordSchema=intermarcXchange&maximumRecords=".$limit."&startRecord=".(($page-1)*$limit)+1;
            // Effectuer la requête
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            curl_close($ch);
 

            $resultat = simplexml_load_string($result, null, 0, 'srw', true);

            $resultat->registerXPathNamespace('srw', 'http://www.loc.gov/zing/srw/');
            $resultat->registerXPathNamespace('ixm', 'http://catalogue.bnf.fr/namespaces/InterXMarc');
            $resultat->registerXPathNamespace('mn', 'http://catalogue.bnf.fr/namespaces/motsnotices');
            $resultat->registerXPathNamespace('sd', 'http://www.loc.gov/zing/srw/diagnostic/');


            $resultats = [];

            $nb_resultats = $resultat->numberOfRecords;

            if ($nb_resultats == 0) {
                return $this->render('search/index.html.twig', [
                    'resultats' => $resultats,
                    'search' => $search,
                    'limit' => $limit,
                    'type' => $type,
                ]);
            }
            // Pages est un tableau qui contient une suite de nombres allant jusqu'au nombre maximum de pages ()
            $pages = range(1, ceil($nb_resultats/$limit));

            foreach ($resultat->records->record as $record) {
                $infos = $record->recordData->children('mxc', true)->record;

                $resultats[] = $api->getInfo($infos);
            }

            return $this->render('search/index.html.twig', [
                'resultats' => $resultats,
                'search' => $search,
                'limit' => (integer)$limit,
                'nb_resultats' => $nb_resultats,
                'page_actuelle' => $page,
                'pages' => $pages,
                'type' => $type,
            ]);
        }



        return $this->render('search/index.html.twig', [
            'search' => '',
            'limit' => 1,
            'type' => 'all',
        ]);
    }
}

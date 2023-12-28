<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Functions\Api;

class ScannerController extends AbstractController
{
    #[Route('/scanner', name: 'app_scanner')]
    public function index(): Response
    {
        if (isset($_POST['codes'])){
            dd($_POST['codes']) ;
        }


        return $this->render('scanner/index.html.twig', [
            'controller_name' => 'ScannerController',
        ]);
    }

    #[Route('/scanner/{code}', name: 'app_scanner_code')]
    // Fonction pour requêter l'API de la BNF
    public function scanner($code, Api $api): Response
    {
        // Faire une requête sur l'api de la bnf
        $url = "https://catalogue.bnf.fr/api/SRU?version=1.2&operation=searchRetrieve&query=(bib.ean%20adj%20%22".$code."%22)%20or%20(bib.isbn%20adj%20%22".$code."%22)&recordSchema=intermarcXchange&maximumRecords=1&startRecord=1";
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

        if ($resultat->numberOfRecords == 0) {
            return $this->json([
                'error' => 'Aucun résultat'
            ]);
        }

        // Retourne du JSON, pour pouvoir l'utiliser en JS, pas besoin de twig
        return $this->json($api->getInfo($resultat->records->record->recordData->children('mxc', true)->record));

    }

}

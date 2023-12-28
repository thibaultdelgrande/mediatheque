<?php

namespace App\Functions;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Api extends AbstractController
{
    public function getInfo($infos): array
    {
        $enregistrement = array();
        $type = $infos->leader->__toString()[22];

        switch ($type) {
            case 'a':
                $enregistrement['type'] = 'Livre';
                break;
            case 'h':
                $enregistrement['type'] = 'Vidéo';
                break;
            default:
                $enregistrement['type'] = 'Autre';
        }


        foreach ($infos->controlfield as $controlfield) {
            if ($controlfield->attributes()['tag'] == '008') {
                $enregistrement['annee'] = (int)substr($controlfield->__toString(), 8, 4);
                $enregistrement['pays'] = substr($controlfield->__toString(), 29, 2);
                $enregistrement['langue'] = substr($controlfield->__toString(), 31, 3);
            }
            if ($controlfield->attributes()['tag'] == '009') {
                // Vidéo
                if ($type == 'h') {
                    $genre = $controlfield->__toString()[4];
                    switch ($genre) {
                        case 'd':
                            $enregistrement['genre'] = 'Non-fiction';
                            break;
                        case 'f':
                            $enregistrement['genre'] = 'Fiction';
                            break;
                        case 'm':
                            $enregistrement['genre'] = 'Musique';
                            break;
                        case 'p':
                            $enregistrement['genre'] = 'Publicité';
                            break;
                        case 'r':
                            $enregistrement['genre'] = 'Recherche formelle';
                            break;
                        case 'v':
                            $enregistrement['genre'] = 'Variété';
                            break;
                        case 'x':
                            $enregistrement['genre'] = 'Inconnu';
                            break;
                        default:
                            $enregistrement['genre'] = 'Non renseigné';
                    }
                    $confidientialite = $controlfield->__toString()[9];
                    switch ($confidientialite) {
                        case '1':
                            $enregistrement['confidentialite'] = 'Communication sur accord du déposant ou de l\'ayant-droit';
                            break;
                        case '3':
                            $enregistrement['confidentialite'] = 'Communication interdite pendant une période déterminée';
                            break;
                        case '4':
                            $enregistrement['confidentialite'] = 'Communication interdite';
                            break;
                        case '5':
                            $enregistrement['confidentialite'] = 'Interdit au moins de 12 ans';
                            break;
                        case '6':
                            $enregistrement['confidentialite'] = 'Interdit au moins de 16 ans';
                            break;
                        case '7':
                            $enregistrement['confidentialite'] = 'Interdit au moins de 18 ans';
                            break;
                    }
                    $reproduction = $controlfield->__toString()[10];
                    switch ($reproduction) {
                        case '1':
                            $enregistrement['reproduction'] = 'Reproduction sur accord de l\'ayant-droit';
                            break;
                        case '2':
                            $enregistrement['reproduction'] = 'Reproduction interdite';
                            break;
                        default:
                            $enregistrement['reproduction'] = 'Reproduction autorisée';
                    }
                }
            }
        }




        foreach ($infos->datafield as $datafield) {
            if ($datafield->attributes()['tag'] == '020') {
                foreach ($datafield->subfield as $subfield) {
                    if ($subfield->attributes()['code'] == 'a') {
                        $enregistrement['isbn'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'd') {
                        $enregistrement['prix'] = $subfield->__toString();
                    }
                }
            }

            if ($datafield->attributes()['tag'] == '038') {
                foreach ($datafield->subfield as $subfield) {
                    if ($subfield->attributes()['code'] == 'a') {
                        $enregistrement['code_barre'] = $subfield->__toString();
                    }
                }
            }

            if ($datafield->attributes()['tag'] == '041') {
                foreach ($datafield->subfield as $subfield) {
                    if ($subfield->attributes()['code'] == 'a') {
                        $enregistrement['langue_texte'][] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'b') {
                        $enregistrement['langue_intermediaire'][] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'c') {
                        $enregistrement['langue_originale'][] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'd') {
                        $enregistrement['langue_resumes'][] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'e') {
                        $enregistrement['langue_sous_titres'][] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'f') {
                        $enregistrement['langue_materiel_accompagnement'][] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'g') {
                        $enregistrement['langue_livret'][] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'h') {
                        $enregistrement['langue_commentaire'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'i') {
                        $enregistrement['langue_parties_annexe'] = $subfield->__toString();
                    }
                }
            }

            if ($datafield->attributes()['tag'] == '100') {
                $auteur = array();
                foreach ($datafield->subfield as $subfield) {
                    if ($subfield->attributes()['code'] == 'a') {
                        $auteur['nom'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'm') {
                        $auteur['prenom'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'd') {
                        $auteur['date'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == '3') {
                        $auteur['code'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == '4') {
                        $auteur['role'] = $this->auteur($subfield->__toString());
                    }
                }
                $enregistrement['auteur'] = $auteur;
            }

            if ($datafield->attributes()['tag'] == '142') {
                foreach ($datafield->subfield as $subfield) {
                    if ($subfield->attributes()['code'] == 'a') {
                        $enregistrement['titre_original'] = $subfield->__toString();
                    }
                }
            }

            if ($datafield->attributes()['tag'] == '245') {
                foreach ($datafield->subfield as $subfield) {
                    if ($subfield->attributes()['code'] == 'a') {
                        $enregistrement['titre'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'b') {
                        $enregistrement['autre_titre'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'c') {
                        $enregistrement['autre_titre_bis'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'e') {
                        $enregistrement['complement_titre'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'j') {
                        $enregistrement['interprète'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'f') {
                        $enregistrement['auteur_short'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'g') {
                        $enregistrement['auteur_secondaire_short'][] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'h') {
                        $enregistrement['numero_serie'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'u') {
                        $enregistrement['classement_serie'] = $subfield->__toString();
                    }
                }
            }

            if ($datafield->attributes()['tag'] == '260') {
                foreach ($datafield->subfield as $subfield) {
                    if ($subfield->attributes()['code'] == 'c') {
                        $enregistrement['editeur'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'd') {
                        $enregistrement['date'] = $subfield->__toString();
                    }
                }
            }

            if ($datafield->attributes()['tag'] == '280') {
                foreach ($datafield->subfield as $subfield) {
                    if ($subfield->attributes()['code'] == 'a') {
                        $enregistrement['indications'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'c') {
                        $enregistrement['autres_indications'][] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'd') {
                        $enregistrement['format'][] = $subfield->__toString();
                    }
                }
            }

            if ($datafield->attributes()['tag'] == '410') {
                $collection = array();
                foreach ($datafield->subfield as $subfield) {
                    if ($subfield->attributes()['code'] == 't') {
                        $collection['titre'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == '3') {
                        $collection['numero'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'd') {
                        $collection['date'] = $subfield->__toString();
                    }
                }
                $enregistrement['collection'] = $collection;
            }

            if ($datafield->attributes()['tag'] == '460') {
                $serie = array();
                foreach ($datafield->subfield as $subfield) {
                    if ($subfield->attributes()['code'] == '3') {
                        $serie['code'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 't') {
                        $serie['titre'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'v') {
                        $serie['numero'] = $subfield->__toString();
                    }
                }
                $enregistrement['serie'] = $serie;
            }

            if ($datafield->attributes()['tag'] == '700') {
                $auteur = array();
                foreach ($datafield->subfield as $subfield) {
                    if ($subfield->attributes()['code'] == 'a') {
                        $auteur['nom'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'm') {
                        $auteur['prenom'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'd') {
                        $auteur['date'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == '3') {
                        $auteur['code'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == '4') {
                        $auteur['role'] = $this->auteur($subfield->__toString());
                    }
                }
                $enregistrement['auteurs'][] = $auteur;
            }

            if ($datafield->attributes()['tag'] == '701') {
                $interprete = array();
                foreach ($datafield->subfield as $subfield) {
                    if ($subfield->attributes()['code'] == 'a') {
                        $interprete['nom'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'm') {
                        $interprete['prenom'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'd') {
                        $interprete['date'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == '3') {
                        $interprete['code'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == '4') {
                        $interprete['role'] = $this->interprete($subfield->__toString());
                    }
                }
                $enregistrement['interpretes'][] = $interprete;
            }

            if ($datafield->attributes()['tag'] == '702') {
                $collaborateur = array();
                foreach ($datafield->subfield as $subfield) {
                    if ($subfield->attributes()['code'] == 'a') {
                        $collaborateur['nom'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'm') {
                        $collaborateur['prenom'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == 'd') {
                        $collaborateur['date'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == '3') {
                        $collaborateur['code'] = $subfield->__toString();
                    }
                    if ($subfield->attributes()['code'] == '4') {
                        $collaborateur['role'] = $this->collaborateur($subfield->__toString());
                    }
                }
                $enregistrement['auteurs_secondaires'][] = $collaborateur;
            }

            if ($datafield->attributes()['tag'] == '830') {
                foreach ($datafield->subfield as $subfield) {
                    if ($subfield->attributes()['code'] == 'a') {
                        $enregistrement['resume'] = $subfield->__toString();
                    }
                }
            }
        }



        return $enregistrement;
    }

    function auteur($code)
    {
        switch ($code) {
            case '0000':
                return 'Auteur';
            case '0001':
                return 'Auteur présumé';
            case '0003':
                return 'Auteur prétendu';
            case '0004':
                return 'Auteur de l\'œuvre reproduite';
            case '0005':
                return 'Auteur de l\'œuvre originale';
            case '0006':
                return 'Auteur de l\'œuvre adaptée';
            case '0007':
                return 'Auteur de l\'œuvre préexistante';
            case '0008':
                return 'Auteur de l\'œuvre musicale';
            case '0009':
                return 'Auteur de l\'œuvre audiovisuelle';
            case '0010':
                return 'Adaptateur';
            case '0011':
                return 'Adaptateur présumé';
            case '0013':
                return 'Adaptateur prétendu';
            case '0020':
                return 'Agence de publicité';
            case '0021':
                return 'Agence de publicité présumée';
            case '0023':
                return 'Agence de publicité prétendue';
            case '0024':
                return 'Agence de publicité pour le document reproduit';
            case '0030':
                return 'Agence photographique';
            case '0031':
                return 'Agence photographique présumée';
            case '0033':
                return 'Agence photographique prétendue';
            case '0034':
                return 'Agence photographique pour le document reproduit';
            case '0040':
                return 'Annotateur';
            case '0041':
                return 'Annotateur présumé';
            case '0043':
                return 'Annotateur prétendu';
            case '0050':
                return 'Arrangeur';
            case '0051':
                return 'Arrangeur présumé';
            case '0053':
                return 'Arrangeur prétendu';
            case '0060':
                return 'Notice bibliographique';
            case '0070':
                return 'Auteur du texte';
            case '0071':
                return 'Auteur présumé du texte';
            case '0072':
                return 'Auteur adapté';
            case '0073':
                return 'Auteur prétendu du texte';
            case '0080':
                return 'Auteur de l\'argument';
            case '0081':
                return 'Auteur présumé de l\'argument';
            case '0083':
                return 'Auteur prétendu de l\'argument';
            case '0090':
                return 'Auteur de l\'animation';
            case '0091':
                return 'Auteur présumé de l\'animation';
            case '0093':
                return 'Auteur prétendu de l\'animation';
            case '0100':
                return 'Auteur de l\'idée originale';
            case '0101':
                return 'Auteur présumé de l\'idée originale';
            case '0103':
                return 'Auteur prétendu de l\'idée originale';
            case '0110':
                return 'Auteur de lettres';
            case '0111':
                return 'Auteur présumé de lettres';
            case '0113':
                return 'Auteur prétendu de lettres';
            case '0120':
                return 'Auteur du commentaire';
            case '0121':
                return 'Auteur présumé du commentaire';
            case '0123':
                return 'Auteur prétendu du commentaire';
            case '0126':
                return 'Auteur de la conférence';
            case '0130':
                return 'Auteur du matériel d\'accompagnement';
            case '0140':
                return 'Calligraphe';
            case '0141':
                return 'Calligraphe supposé';
            case '0143':
                return 'Calligraphe prétendu';
            case '0144':
                return 'Calligraphe du document reproduit';
            case '0150':
                return 'Cartographe';
            case '0151':
                return 'Cartographe présumé';
            case '0152':
                return 'Cartographe du modèle';
            case '0153':
                return 'Cartographe prétendu';
            case '0154':
                return 'Cartographe du document reproduit';
            case '0160':
                return 'Chorégraphe';
            case '0161':
                return 'Chorégraphe présumé';
            case '0163':
                return 'Chorégraphe prétendu';
            case '0170':
                return 'Collaborateur';
            case '0171':
                return 'Collaborateur présumé';
            case '0173':
                return 'Collaborateur prétendu';
            case '0180':
                return 'Collecteur';
            case '0190':
                return 'Commanditaire du contenu';
            case '0200':
                return 'Commissaire d\'exposition';
            case '0210':
                return 'Compilateur';
            case '0220':
                return 'Compositeur';
            case '0221':
                return 'Compositeur présumé';
            case '0222':
                return 'Compositeur de l\'œuvre adaptée';
            case '0223':
                return 'Compositeur prétendu';
            case '0230':
                return 'Concepteur';
            case '0240':
                return 'Chef de projet informatique';
            case '0250':
                return 'Conseiller scientifique';
            case '0260':
                return 'Continuateur';
            case '0261':
                return 'Continuateur présumé';
            case '0263':
                return 'Continuateur prétendu';
            case '0270':
                return 'Copiste';
            case '0271':
                return 'Copiste présumé';
            case '0273':
                return 'Copiste prétendu';
            case '0280':
                return 'Correcteur';
            case '0290':
                return 'Dédicataire';
            case '0300':
                return 'Dédicateur';
            case '0310':
                return 'Dessinateur';
            case '0311':
                return 'Dessinateur présumé';
            case '0312':
                return 'Dessinateur du modèle';
            case '0313':
                return 'Dessinateur prétendu';
            case '0314':
                return 'Dessinateur de l\'œuvre reproduite';
            case '0320':
                return 'Destinataire de lettres';
            case '0321':
                return 'Destinataire de lettres présumé';
            case '0330':
                return 'Directeur de publication';
            case '0340':
                return 'Dialoguiste';
            case '0341':
                return 'Dialoguiste présumé';
            case '0343':
                return 'Dialoguiste prétendu';
            case '0350':
                return 'Dramaturge';
            case '0360':
                return 'Éditeur scientifique';
            case '0365':
                return 'Expert';
            case '0370':
                return 'Autorité émettrice de monnaie';
            case '0371':
                return 'Autorité émettrice de monnaie présumée';
            case '0375':
                return 'Émetteur';
            case '0376':
                return 'Émetteur douteux';
            case '0380':
                return 'Graphiste';
            case '0390':
                return 'Géodésien';
            case '0400':
                return 'Géomètre-arpenteur';
            case '0410':
                return 'Graveur';
            case '0411':
                return 'Graveur présumé';
            case '0412':
                return 'Graveur du modèle';
            case '0413':
                return 'Graveur prétendu';
            case '0414':
                return 'Graveur de l\'œuvre reproduite';
            case '0420':
                return 'Graveur en lettres';
            case '0421':
                return 'Graveur en lettres présumé';
            case '0422':
                return 'Graveur en lettres du modèle';
            case '0423':
                return 'Graveur en lettres prétendu';
            case '0424':
                return 'Graveur en lettres de l\'œuvre reproduite';
            case '0430':
                return 'Harmonisateur';
            case '0440':
                return 'Illustrateur';
            case '0441':
                return 'Illustrateur présumé';
            case '0443':
                return 'Illustrateur prétendu';
            case '0450':
                return 'Interviewer';
            case '0460':
                return 'Reportage';
            case '0470':
                return 'Librettiste';
            case '0471':
                return 'Librettiste présumé';
            case '0473':
                return 'Librettiste prétendu';
            case '0480':
                return 'Lithographe';
            case '0481':
                return 'Lithographe présumé';
            case '0483':
                return 'Lithographe prétendu';
            case '0484':
                return 'Lithographe de l\'œuvre reproduite';
            case '0485':
                return 'Sérigraphe';
            case '0490':
                return 'Médailleur';
            case '0500':
                return 'Metteur en scène';
            case '0505':
                return 'Assistant metteur en scène';
            case '0506':
                return 'Direction du doublage';
            case '0510':
                return 'Parolier';
            case '0520':
                return 'Peintre';
            case '0521':
                return 'Peintre présumé';
            case '0522':
                return 'Peintre du modèle';
            case '0523':
                return 'Peintre prétendu';
            case '0524':
                return 'Peintre de l\'œuvre reproduite';
            case '0530':
                return 'Photographe';
            case '0531':
                return 'Photographe présumé';
            case '0532':
                return 'Photographe, auteur du modèle';
            case '0533':
                return 'Photographe prétendu';
            case '0534':
                return 'Photographe de l\'œuvre reproduite';
            case '0540':
                return 'Postfacier';
            case '0550':
                return 'Préfacier';
            case '0560':
                return 'Présentateur';
            case '0570':
                return 'Producteur artistique';
            case '0580':
                return 'Producteur de fonds d\'archives';
            case '0590':
                return 'Développeur';
            case '0600':
                return 'Programmeur';
            case '0610':
                return 'Réalisateur';
            case '0611':
                return 'Réalisateur présumé';
            case '0613':
                return 'Réalisateur prétendu';
            case '0620':
                return 'Rédacteur';
            case '0630':
                return 'Scénariste';
            case '0640':
                return 'Sculpteur';
            case '0641':
                return 'Sculpteur présumé';
            case '0642':
                return 'Sculpteur du modèle';
            case '0643':
                return 'Sculpteur prétendu';
            case '0644':
                return 'Sculpteur de l\'œuvre reproduite';
            case '0650':
                return 'Signataire';
            case '0660':
                return 'Technicien graphique';
            case '0670':
                return 'Testeur';
            case '0680':
                return 'Traducteur';
            case '0690':
                return 'Créateur de spectacle';
            case '0700':
                return 'Auteur de la collation';
            case '0710':
                return 'Enlumineur';
            case '0711':
                return 'Enlumineur présumé';
            case '0712':
                return 'Enlumineur du modèle';
            case '0713':
                return 'Enlumineur prétendu';
            case '0714':
                return 'Enlumineur de l\'œuvre reproduite';
            case '0720':
                return 'Auteur de la recension';
            case '0730':
                return 'Transcripteur';
            case '0740':
                return 'Monétaire';
            case '0750':
                return 'Orfèvre';
            case '0760':
                return 'Graveur de coin';
            case '0770':
                return 'Réalisation de la basse continue';
            case '0780':
                return 'Orchestrateur';
            case '0790':
                return 'Auteur de la réduction musicale';
            case '0800':
                return 'Auteur du slogan';
            case '0804':
                return 'Auteur du slogan de l\'œuvre reproduite';
            case '0810':
                return 'Directeur d\'atelier';
            case '0820':
                return 'Rubricateur';
            case '0830':
                return 'Lettriste';
            case '0840':
                return 'Secrétaire';
            case '0850':
                return 'Notaire, tabellion';
            case '0860':
                return 'Greffier';
            case '0870':
                return 'Fondateur de la publication';
            case '0891':
                return 'Plasticien';
            case '0900':
                return 'Programmateur';
            case '0910':
                return 'Chef de la mission';
            case '0980':
                return 'Auteur ou responsable intellectuel (autre)';
            case '0990':
                return 'Auteur ou responsable intellectuel';
            case '4010':
                return 'Ancien possesseur';
            case '4020':
                return 'Auteur de l\'envoi';
            case '4030':
                return 'Collectionneur';
            case '4040':
                return 'Donateur';
            case '4050':
                return 'Doreur';
            case '4060':
                return 'Inventeur';
            case '4070':
                return 'Parapheur';
            case '4080':
                return 'Destinataire de l\'envoi';
            case '4090':
                return 'Ancien détenteur';
            case '4100':
                return 'Fondateur de waqf';
            case '4110':
                return 'Administrateur de waqf';
            case '4120':
                return 'Lecteur';
            case '4130':
                return 'Musmi?';
            case '4140':
                return 'Relieur';
            case '4150':
                return 'Vendeur';
            case '4160':
                return 'Bénéficiaire de waqf';
            case '4170':
                return 'Annotations manuscrites';
            case '4180':
                return 'Auteur de la pièce jointe';
            case '4190':
                return 'Destinataire de la pièce jointe';
            case '4200':
                return 'Illustrateur de l\'exemplaire';
            case '4210':
                return 'Déposant';
            case '4220':
                return 'Intermédiaire commercial';
            case '4230':
                return 'Mécène';
            case '4240':
                return 'Commanditaire de la reliure';
            case '4980':
                return 'Intervenant sur l\'exemplaire (autre)';
            case '4990':
                return 'Intervenant sur l\'exemplaire';
            case '9990':
                return 'Fonction indéterminé';
            default:
                return '';
        }
    }

    function interprete($code)
    {
        switch ($code) {
            case '1010':
                return 'Acteur';
            case '1011':
                return 'Acteur présumé';
            case '1013':
                return 'Acteur prétendu';
            case '1017':
                return 'Humoriste';
            case '1018':
                return 'Chansonnier';
            case '1020':
                return 'Artiste de cirque';
            case '1030':
                return 'Chant';
            case '1031':
                return 'Chanteur présumé';
            case '1033':
                return 'Chanteur prétendu';
            case '1039':
                return 'Chant (sons traités par l\'électronique)';
            case '1040':
                return 'Direction d\'orchestre';
            case '1050':
                return 'Marionnettiste';
            case '1060':
                return 'Danse';
            case '1080':
                return 'Direction de chœur';
            case '1090':
                return 'Mime';
            case '1100':
                return 'Instrumentiste';
            case '1101':
                return 'Instrumentiste présumé';
            case '1103':
                return 'Instrumentiste prétendu';
            case '1108':
                return 'Instrumentiste (musique ethnique)';
            case '1110':
                return 'Voix parlée';
            case '1119':
                return 'Voix parlée (sons traités par l\'électronique)';
            case '1120':
                return 'Soprano';
            case '1129':
                return 'Soprano (sons traités par l\'électronique)';
            case '1130':
                return 'Mezzo-soprano';
            case '1139':
                return 'Mezzo-soprano (sons traités par l\'électronique)';
            case '1140':
                return 'Alto (voix)';
            case '1149':
                return 'Alto (voix ; sons traités par l\'électronique)';
            case '1150':
                return 'Ténor';
            case '1159':
                return 'Ténor (sons traités par l\'électronique)';
            case '1160':
                return 'Baryton (voix)';
            case '1169':
                return 'Baryton (voix ; sons traités par l\'électronique)';
            case '1170':
                return 'Baryton-basse';
            case '1179':
                return 'Baryton-basse (sons traités par l\'électronique)';
            case '1180':
                return 'Basse (voix)';
            case '1189':
                return 'Basse (voix ; sons traités par l\'électronique)';
            case '1190':
                return 'Contre-ténor';
            case '1197':
                return 'Haute-contre';
            case '1199':
                return 'Contre-ténor (sons traités par l\'électronique)';
            case '1200':
                return 'Voix chantée d\'enfant';
            case '1210':
                return 'Violon';
            case '1217':
                return 'Cordes frottées (divers)';
            case '1218':
                return 'Cordes frottées (musique ethnique)';
            case '1219':
                return 'Violon électrique';
            case '1220':
                return 'Alto (instrument)';
            case '1229':
                return 'Alto (instrument ; traité par l\'électronique)';
            case '1230':
                return 'Violoncelle';
            case '1239':
                return 'Violoncelle électrique';
            case '1240':
                return 'Contrebasse';
            case '1249':
                return 'Contrebasse électrique';
            case '1250':
                return 'Basse de viole';
            case '1257':
                return 'Viole';
            case '1258':
                return 'Cordophones divers (musique ethnique)';
            case '1260':
                return 'Viole d\'amour';
            case '1268':
                return 'Hardingfele';
            case '1270':
                return 'Harpe';
            case '1277':
                return 'Harpes (diverses)';
            case '1278':
                return 'Harpes (musique ethnique)';
            case '1280':
                return 'Guitare';
            case '1287':
                return 'Guitares (diverses)';
            case '1288':
                return 'Guitares (musique ethnique)';
            case '1289':
                return 'Guitare électrique';
            case '1290':
                return 'Guitare basse';
            case '1299':
                return 'Guitare basse électrique';
            case '1300':
                return 'Guitare hawaïenne';
            case '1309':
                return 'Pedal steel guitar';
            case '1310':
                return 'Luth';
            case '1317':
                return 'Luths (divers)';
            case '1318':
                return 'Luths (musique ethnique)';
            case '1320':
                return 'Théorbe';
            case '1330':
                return 'Mandoline';
            case '1337':
                return 'Instruments à plectre (divers)';
            case '1340':
                return 'Vihuela';
            case '1350':
                return 'Banjo';
            case '1357':
                return 'Cordes pincées (divers)';
            case '1358':
                return 'Cordes pincées (musique ethnique)';
            case '1360':
                return 'Cithare';
            case '1367':
                return 'Cithares (diverses)';
            case '1368':
                return 'Cithares (musique ethnique)';
            case '1370':
                return 'Cymbalum';
            case '1377':
                return 'Cordes frappées (divers)';
            case '1378':
                return 'Cordes frappées (musique ethnique)';
            case '1380':
                return 'Flûte';
            case '1387':
                return 'Bois (divers)';
            case '1388':
                return 'Flûtes (musique ethnique)';
            case '1389':
                return 'Flûte (sons traités par l\'électronique)';
            case '1390':
                return 'Piccolo';
            case '1400':
                return 'Flûte à bec';
            case '1407':
                return 'Flûtes à bec (diverses)';
            case '1410':
                return 'Flûte de Pan';
            case '1418':
                return 'Flûtes de Pan (musique ethnique)';
            case '1420':
                return 'Clarinette';
            case '1427':
                return 'Instruments à anche (divers)';
            case '1428':
                return 'Instruments à anche (musique ethnique)';
            case '1430':
                return 'Hautbois';
            case '1437':
                return 'Instruments à vent (divers)';
            case '1438':
                return 'Instruments à vent divers (musique ethnique)';
            case '1440':
                return 'Cor anglais';
            case '1450':
                return 'Saxophone';
            case '1459':
                return 'Lyricon';
            case '1460':
                return 'Basson';
            case '1470':
                return 'Trompette';
            case '1477':
                return 'Cuivres (divers)';
            case '1478':
                return 'Trompes (musique ethnique)';
            case '1480':
                return 'Bugle';
            case '1490':
                return 'Cornet à pistons';
            case '1500':
                return 'Cornet à bouquin';
            case '1510':
                return 'Cor';
            case '1520':
                return 'Trombone';
            case '1527':
                return 'Trombones (divers)';
            case '1530':
                return 'Tuba';
            case '1537':
                return 'Tubas (divers)';
            case '1540':
                return 'Mirliton';
            case '1550':
                return 'Harmonica';
            case '1557':
                return 'Instruments à anche libre (divers)';
            case '1558':
                return 'Instruments à anche libre (musique ethnique)';
            case '1560':
                return 'Piano';
            case '1567':
                return 'Claviers (divers)';
            case '1569':
                return 'Piano électrique';
            case '1570':
                return 'Pianoforte';
            case '1580':
                return 'Clavecin';
            case '1587':
                return 'Clavecins (divers)';
            case '1590':
                return 'Orgue';
            case '1597':
                return 'Orgues (divers)';
            case '1598':
                return 'Orgue (musique ethnique)';
            case '1599':
                return 'Orgue électronique';
            case '1600':
                return 'Orgue mécanique';
            case '1607':
                return 'Orgues mécaniques (divers)';
            case '1610':
                return 'Harmonium';
            case '1620':
                return 'Clavicorde';
            case '1630':
                return 'Accordéon';
            case '1637':
                return 'Accordéons (divers)';
            case '1638':
                return 'Accordéon (musique ethnique)';
            case '1640':
                return 'Vielle à roue';
            case '1649':
                return 'Vielle à roue électrique';
            case '1650':
                return 'Batterie';
            case '1651':
                return 'Batterie (interprète présumé)';
            case '1653':
                return 'Batterie (interprète prétendu)';
            case '1657':
                return 'Membranophones (divers)';
            case '1658':
                return 'Membranophones (musique ethnique)';
            case '1659':
                return 'Batterie électrique';
            case '1660':
                return 'Tambour';
            case '1667':
                return 'Percussions';
            case '1668':
                return 'Percussions (musique ethnique)';
            case '1670':
                return 'Timbales';
            case '1680':
                return 'Xylophone';
            case '1688':
                return 'Xylophones (musique ethnique)';
            case '1690':
                return 'Marimba';
            case '1700':
                return 'Vibraphone';
            case '1707':
                return 'Métallophones (divers)';
            case '1710':
                return 'Carillon';
            case '1717':
                return 'Idiophones par percussion (divers)';
            case '1718':
                return 'Idiophones par percussion (musique ethnique)';
            case '1720':
                return 'Claquettes';
            case '1728':
                return 'Idiophones secoués (musique ethnique)';
            case '1730':
                return 'Guimbarde';
            case '1738':
                return 'Idiophones pincés (musique ethnique)';
            case '1740':
                return 'Harmonica de verre';
            case '1747':
                return 'Cristallophones';
            case '1748':
                return 'Idiophones frottés (musique ethnique)';
            case '1750':
                return 'Ondes Martenot';
            case '1760':
                return 'Synthétiseur';
            case '1767':
                return 'Instrument électronique';
            case '1770':
                return 'Instrument électronique (non autonome)';
            case '1777':
                return 'Sculptures et environnements sonores';
            case '1780':
                return 'Orchestre';
            case '1787':
                return 'Ensembles (divers)';
            case '1790':
                return 'Ensemble instrumental';
            case '1797':
                return 'Groupe instrumental';
            case '1798':
                return 'Ensemble instrumental (musique ethnique)';
            case '1800':
                return 'Chœur mixte';
            case '1807':
                return 'Chœur à voix égales';
            case '1810':
                return 'Ensemble vocal';
            case '1817':
                return 'Groupe vocal';
            case '1818':
                return 'Ensemble vocal (musique ethnique)';
            case '1820':
                return 'Ensemble vocal et instrumental';
            case '1827':
                return 'Groupe vocal et instrumental';
            case '1828':
                return 'Ensemble vocal et instrumental (musique ethnique)';
            case '1830':
                return 'Siffleur';
            case '1837':
                return 'Bruits corporels';
            case '1840':
                return 'Bruitages';
            case '1850':
                return 'Disc jockey';
            case '1860':
                return 'Remixeur';
            case '1870':
                return 'Ensemble de cordes';
            case '1878':
                return 'Ensemble de cordes (musique ethnique)';
            case '1880':
                return 'Ensemble à vent';
            case '1888':
                return 'Ensemble à vent (musique ethnique)';
            case '1890':
                return 'Ensemble de cuivres';
            case '1898':
                return 'Ensemble de cuivres (musique ethnique)';
            case '1900':
                return 'Piano à 4 mains';
            case '1910':
                return 'Pianoforte à 4 mains';
            case '1920':
                return 'Célesta';
            case '1930':
                return 'Cornemuse';
            case '1937':
                return 'Cornemuses (diverses)';
            case '1938':
                return 'Cornemuses (musique ethnique)';
            case '1940':
                return 'Washboard';
            case '1947':
                return 'Idiophones raclés (divers)';
            case '1948':
                return 'Idiophones raclés (musique ethnique)';
            case '1980':
                return 'Interprète (autre)';
            case '1990':
                return 'Interprète';
            case '9990':
                return 'Fonction indéterminée';
            default:
                return '';
        }
    }

    function collaborateur($code) {
        switch ($code){
            case '2010':
                return 'Accessoiriste';
            case '2020':
                return 'Assistant réalisateur';
            case '2030':
                return 'Bruiteur';
            case '2040':
                return 'Cascades et batailles';
            case '2045':
                return 'Conseiller pour les cascades';
            case '2046':
                return 'Cascadeur';
            case '2050':
                return 'Coloriste';
            case '2060':
                return 'Conseiller technique';
            case '2070':
                return 'Contretypeur';
            case '2080':
                return 'Costumes';
            case '2085':
                return 'Chef costumier';
            case '2086':
                return 'Réalisation des costumes';
            case '2090':
                return 'Décors';
            case '2095':
                return 'Chef décorateur';
            case '2096':
                return 'Ensemblier';
            case '2100':
                return 'Directeur artistique';
            case '2110':
                return 'Directeur de ballet';
            case '2120':
                return 'Directeur musical';
            case '2130':
                return 'Documentaliste';
            case '2140':
                return 'Éclairages';
            case '2145':
                return 'Conception des éclairages';
            case '2146':
                return 'Régisseur lumières';
            case '2150':
                return 'Effets spéciaux';
            case '2160':
                return 'Faussaire';
            case '2170':
                return 'Fondeur';
            case '2180':
                return 'Illustration sonore';
            case '2190':
                return 'Maquettiste';
            case '2200':
                return 'Maquillage';
            case '2205':
                return 'Chef maquilleur';
            case '2206':
                return 'Maquilleur';
            case '2210':
                return 'Création des marionnettes';
            case '2215':
                return 'Conception des marionnettes';
            case '2216':
                return 'Réalisation des marionnettes';
            case '2220':
                return 'Création des masques';
            case '2230':
                return 'Montage';
            case '2235':
                return 'Chef monteur image';
            case '2240':
                return 'Photographe de plateau';
            case '2250':
                return 'Prises de vue';
            case '2255':
                return 'Directeur de la photographie';
            case '2256':
                return 'Opérateur prises de vue';
            case '2260':
                return 'Régisseur';
            case '2266':
                return 'Technicien régie';
            case '2270':
                return 'Relieur';
            case '2280':
                return 'Scénographe';
            case '2290':
                return 'Script';
            case '2300':
                return 'Son';
            case '2305':
                return 'Ingénieur du son';
            case '2306':
                return 'Opérateur du son';
            case '2310':
                return 'Typographe';
            case '2320':
                return 'Régleur';
            case '2330':
                return 'Kitabdar';
            case '2340':
                return 'Scripteur';
            case '2350':
                return 'Cartonnage d\'éditeur';
            case '2360':
                return 'Graphiste';
            case '2370':
                return 'Régisseur son (théâtre)';
            case '2980':
                return 'Collaborateur technico-artistique (autre)';
            case '2990':
                return 'Collaborateur technico-artistique';
            case '9990':
                return 'Fonction indéterminée';
            default:    
                return '';
        }
    }
}

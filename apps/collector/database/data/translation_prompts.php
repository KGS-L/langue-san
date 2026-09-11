<?php

/**
 * 500 prompts de traduction français → San.
 *
 * 25 thèmes × (14 mots/expressions + 6 phrases) = 500 prompts.
 * Aucune traduction San n'est incluse ici : elles doivent venir des locuteurs et validateurs.
 */
return [
    'salutations' => [
        'prefix' => 'SAL',
        'words' => ['Bonjour', 'Bonsoir', 'Merci', "S'il vous plaît", 'Au revoir', 'Bienvenue', 'Pardon', 'Oui', 'Non', "D'accord", 'Excusez-moi', 'Félicitations', 'Bon courage', 'À bientôt'],
        'sentences' => ['Comment vas-tu ?', 'Je vais bien.', 'À demain.', 'Merci beaucoup.', 'Bienvenue chez nous.', 'Que Dieu vous accompagne.'],
    ],
    'presentation-identite' => [
        'prefix' => 'PRE',
        'words' => ['Nom', 'Prénom', 'Homme', 'Femme', 'Enfant', 'Village', 'Âge', 'Famille', 'Origine', 'Adresse', 'Nationalité', 'Profession', 'Marié', 'Célibataire'],
        'sentences' => ["Comment t'appelles-tu ?", "Je m'appelle …", 'Où habites-tu ?', "D'où viens-tu ?", 'Quel âge as-tu ?', 'Quel travail fais-tu ?'],
    ],
    'famille' => [
        'prefix' => 'FAM',
        'words' => ['Père', 'Mère', 'Frère', 'Sœur', 'Enfant', 'Mari', 'Épouse', 'Grand-père', 'Grand-mère', 'Oncle', 'Tante', 'Cousin', 'Fils', 'Fille'],
        'sentences' => ['Voici mon père.', 'Ma mère est à la maison.', "J'ai deux enfants.", 'Mon frère est parti au marché.', 'Ma grand-mère parle San.', 'Toute la famille est réunie.'],
    ],
    'nombres' => [
        'prefix' => 'NUM',
        'words' => ['Un', 'Deux', 'Trois', 'Quatre', 'Cinq', 'Six', 'Sept', 'Huit', 'Neuf', 'Dix', 'Vingt', 'Cent', 'Mille', 'Moitié'],
        'sentences' => ['Nous sommes trois.', "J'en veux deux.", 'Il y a cinq personnes.', 'Donne-moi dix francs.', 'Nous avons vingt sacs.', 'Partage cela en deux.'],
    ],
    'temps-jours' => [
        'prefix' => 'TEM',
        'words' => ["Aujourd'hui", 'Demain', 'Hier', 'Matin', 'Midi', 'Soir', 'Nuit', 'Jour', 'Semaine', 'Mois', 'Année', 'Lundi', 'Dimanche', 'Maintenant'],
        'sentences' => ['Je viens demain.', 'Il est parti ce matin.', "Nous travaillons aujourd'hui.", 'Je reviendrai la semaine prochaine.', 'Il est déjà tard.', 'Attends-moi un moment.'],
    ],
    'nourriture' => [
        'prefix' => 'NOU',
        'words' => ['Eau', 'Riz', 'Mil', 'Maïs', 'Viande', 'Poisson', 'Sauce', 'Pain', 'Sel', 'Sucre', 'Huile', 'Manger', 'Boire', 'Faim'],
        'sentences' => ["Je veux boire de l'eau.", 'Nous allons manger.', 'La nourriture est prête.', 'Ajoute un peu de sel.', "J'ai faim.", "Donne-moi de l'eau, s'il te plaît."],
    ],
    'maison' => [
        'prefix' => 'MAI',
        'words' => ['Maison', 'Porte', 'Cour', 'Chambre', 'Cuisine', 'Toit', 'Mur', 'Lit', 'Chaise', 'Table', 'Feu', 'Lampe', 'Dormir', 'Balayer'],
        'sentences' => ['Entre dans la maison.', 'Ferme la porte.', 'Les enfants dorment.', 'Assieds-toi sur la chaise.', 'Le feu est allumé.', 'Balaye la cour.'],
    ],
    'marche-commerce' => [
        'prefix' => 'MAR',
        'words' => ['Marché', 'Argent', 'Prix', 'Acheter', 'Vendre', 'Donner', 'Cher', 'Bon marché', 'Monnaie', 'Client', 'Vendeur', 'Sac', 'Peser', 'Payer'],
        'sentences' => ['Combien ça coûte ?', 'Je veux acheter ceci.', "C'est trop cher.", 'Donnez-moi la monnaie.', 'Je paierai demain.', 'Pèse-moi un kilo.'],
    ],
    'deplacements' => [
        'prefix' => 'DEP',
        'words' => ['Aller', 'Venir', 'Partir', 'Arriver', 'Route', 'Vélo', 'Moto', 'Voiture', 'Marcher', 'Voyager', 'Retourner', 'Attendre', 'Traverser', 'Transport'],
        'sentences' => ['Où vas-tu ?', 'Je vais au village.', 'Viens ici.', 'Nous partons maintenant.', 'Attends-moi sur la route.', 'Il est arrivé hier.'],
    ],
    'ecole' => [
        'prefix' => 'ECO',
        'words' => ['École', 'Enseignant', 'Élève', 'Cahier', 'Livre', 'Stylo', 'Tableau', 'Écrire', 'Lire', 'Comprendre', 'Apprendre', 'Question', 'Réponse', 'Examen'],
        'sentences' => ['Ouvre ton cahier.', 'Lis cette phrase.', "Je n'ai pas compris.", 'Écris ton nom.', 'Le maître pose une question.', "Les enfants vont à l'école."],
    ],
    'travail' => [
        'prefix' => 'TRA',
        'words' => ['Travail', 'Métier', 'Atelier', 'Tailleur', 'Commerçant', 'Ouvrier', 'Patron', 'Salaire', 'Repos', 'Commencer', 'Finir', 'Aider', 'Apprendre', 'Fatigué'],
        'sentences' => ['Je vais au travail.', "Il travaille dans un atelier.", "Je commence à huit heures.", 'Il a fini son travail.', 'Je me repose après le travail.', 'Quel est ton métier ?'],
    ],
    'corps-sante' => [
        'prefix' => 'SNT',
        'words' => ['Tête', 'Œil', 'Oreille', 'Nez', 'Bouche', 'Main', 'Pied', 'Ventre', 'Dos', 'Malade', 'Douleur', 'Fièvre', 'Médicament', 'Hôpital'],
        'sentences' => ["J'ai mal à la tête.", 'Mon enfant a de la fièvre.', 'Je vais au centre de santé.', 'Où as-tu mal ?', 'Prends ce médicament.', "Il se sent mieux aujourd'hui."],
    ],
    'vetements-apparence' => [
        'prefix' => 'VET',
        'words' => ['Habit', 'Chemise', 'Pantalon', 'Robe', 'Chaussure', 'Chapeau', 'Tissu', 'Coudre', 'Laver', 'Porter', 'Propre', 'Sale', 'Grand', 'Petit'],
        'sentences' => ['Cette chemise est propre.', 'Je veux coudre une robe.', 'Mets tes chaussures.', 'Ce pantalon est trop grand.', 'Lave les habits.', "J'aime ce tissu."],
    ],
    'agriculture-champs' => [
        'prefix' => 'AGR',
        'words' => ['Champ', 'Terre', 'Mil', 'Maïs', 'Sorgho', 'Arachide', 'Graine', 'Semer', 'Cultiver', 'Sarcler', 'Récolter', 'Houe', 'Pluie', 'Grenier'],
        'sentences' => ['Nous allons au champ.', 'La pluie a commencé.', "Il faut semer aujourd'hui.", 'Le mil est prêt à être récolté.', 'Mets les grains dans le grenier.', 'Nous avons bien travaillé au champ.'],
    ],
    'elevage-animaux' => [
        'prefix' => 'ELE',
        'words' => ['Bœuf', 'Vache', 'Mouton', 'Chèvre', 'Poulet', 'Chien', 'Chat', 'Âne', 'Porc', 'Troupeau', 'Nourrir', 'Attacher', 'Vendre', 'Animal'],
        'sentences' => ['Les chèvres sont dehors.', "Donne de l'eau aux animaux.", 'Le troupeau est revenu.', 'Attache le mouton.', 'Nous avons vendu une vache.', 'Le chien garde la maison.'],
    ],
    'nature-environnement' => [
        'prefix' => 'NAT',
        'words' => ['Arbre', 'Herbe', 'Forêt', 'Rivière', 'Eau', 'Terre', 'Pierre', 'Sable', 'Colline', 'Ombre', 'Bois', 'Feuille', 'Fleur', 'Fruit'],
        'sentences' => ["Cet arbre donne beaucoup d'ombre.", 'La rivière est loin.', 'Ramasse du bois.', "Il y a beaucoup d'arbres ici.", "L'eau est propre.", "Assieds-toi sous l'arbre."],
    ],
    'meteo-saisons' => [
        'prefix' => 'MET',
        'words' => ['Pluie', 'Soleil', 'Vent', 'Chaleur', 'Froid', 'Nuage', 'Orage', 'Saison', 'Hivernage', 'Sécheresse', 'Poussière', 'Ciel', 'Mouillé', 'Sec'],
        'sentences' => ['Il va pleuvoir.', "Il fait très chaud aujourd'hui.", 'Le vent souffle fort.', 'Le ciel est couvert.', 'La saison des pluies commence.', 'La terre est sèche.'],
    ],
    'village-communaute' => [
        'prefix' => 'VIL',
        'words' => ['Village', 'Quartier', 'Chef', 'Voisin', 'Réunion', 'Communauté', 'Jeune', 'Ancien', 'Place', 'Aider', 'Partager', 'Visite', 'Conseil', 'Habitant'],
        'sentences' => ['Il y a une réunion au village.', 'Les voisins sont venus nous aider.', 'Le chef du village est arrivé.', 'Nous allons rendre visite à la famille.', 'Les anciens se sont réunis.', 'Tout le quartier est informé.'],
    ],
    'ceremonies-traditions' => [
        'prefix' => 'CER',
        'words' => ['Mariage', 'Baptême', 'Funérailles', 'Fête', 'Danse', 'Musique', 'Tradition', 'Coutume', 'Ancêtre', 'Masque', 'Tambour', 'Chant', 'Cadeau', 'Cérémonie'],
        'sentences' => ['Le mariage aura lieu demain.', 'Les gens dansent pendant la fête.', 'On a préparé le repas pour la cérémonie.', "Les anciens racontent l'histoire du village.", 'Le tambour a commencé à jouer.', 'Toute la famille est venue aux funérailles.'],
    ],
    'emotions-etats' => [
        'prefix' => 'EMO',
        'words' => ['Content', 'Triste', 'Fâché', 'Peur', 'Fatigué', 'Heureux', 'Calme', 'Inquiet', 'Honte', 'Courage', 'Surpris', 'Faim', 'Soif', 'Sommeil'],
        'sentences' => ['Je suis content de te voir.', 'Pourquoi es-tu triste ?', "N'aie pas peur.", 'Je suis très fatigué.', "L'enfant a sommeil.", 'Nous sommes inquiets pour lui.'],
    ],
    'actions-quotidiennes' => [
        'prefix' => 'ACT',
        'words' => ['Se lever', 'Se coucher', 'Se laver', 'Manger', 'Boire', "S'asseoir", 'Marcher', 'Courir', 'Porter', 'Prendre', 'Poser', 'Ouvrir', 'Fermer', 'Attendre'],
        'sentences' => ['Je me lève tôt le matin.', 'Va te laver.', 'Pose cela ici.', 'Ouvre la fenêtre.', 'Ferme la porte avant de partir.', 'Nous mangeons ensemble.'],
    ],
    'directions-lieux' => [
        'prefix' => 'DIR',
        'words' => ['Ici', 'Là-bas', 'Devant', 'Derrière', 'Gauche', 'Droite', 'Nord', 'Sud', 'Près', 'Loin', 'Dedans', 'Dehors', 'Chemin', 'Carrefour'],
        'sentences' => ['Tourne à gauche.', "C'est près d'ici.", 'Continue tout droit.', 'La maison est derrière le marché.', 'Attends-moi au carrefour.', "Le village est loin d'ici."],
    ],
    'administration-services' => [
        'prefix' => 'ADM',
        'words' => ['Mairie', 'État civil', 'Carte', 'Document', 'Signature', 'Photo', 'Bureau', 'Agent', 'Police', 'Justice', 'Formulaire', 'Numéro', 'Attestation', 'Dossier'],
        'sentences' => ["Je veux faire une carte d'identité.", 'Où est la mairie ?', "Signez ici, s'il vous plaît.", 'Il manque un document.', 'Donnez-moi votre numéro.', 'Je viens chercher mon attestation.'],
    ],
    'technologie-communication' => [
        'prefix' => 'TEC',
        'words' => ['Téléphone', 'Appel', 'Message', 'Numéro', 'Internet', 'Photo', 'Vidéo', 'Radio', 'Télévision', 'Batterie', 'Charger', 'Envoyer', 'Recevoir', 'Réseau'],
        'sentences' => ['Donne-moi ton numéro de téléphone.', "Je vais t'appeler demain.", 'Envoie-moi un message.', "Il n'y a pas de réseau.", "Mon téléphone n'a plus de batterie.", "J'ai reçu ta photo."],
    ],
    'relations-conversation' => [
        'prefix' => 'REL',
        'words' => ['Parler', 'Écouter', 'Demander', 'Répondre', 'Comprendre', 'Répéter', 'Expliquer', 'Raconter', 'Dire', 'Entendre', 'Connaître', 'Savoir', 'Accepter', 'Refuser'],
        'sentences' => ["Parle plus lentement, s'il te plaît.", 'Peux-tu répéter ?', 'Je comprends ce que tu dis.', 'Explique-moi encore.', 'Je ne sais pas.', "Raconte-moi ce qui s'est passé."],
    ],
];

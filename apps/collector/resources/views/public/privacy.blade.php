@extends('public.legal-layout')

@section('title', 'Confidentialité')
@section('description', 'Politique de confidentialité du collecteur Langue SAN.')
@section('heading', 'Politique de confidentialité')

@section('content')
<p class="text-muted">Version de travail du MVP — septembre 2026. Cette page décrit les principes actuellement appliqués par le collecteur Langue SAN. Elle devra être relue et complétée avant un lancement public à grande échelle.</p>

<h2>1. Données collectées</h2>
<p>Il est possible de contribuer sans créer de compte. Dans ce cas, le système crée un profil pseudonymisé avec un identifiant public et associe la session à un jeton conservé dans le navigateur. Seul le hash de ce jeton est stocké côté serveur.</p>
<p>Selon le parcours, nous pouvons enregistrer la localité déclarée, le niveau de pratique du San, la capacité à l’écrire, les traductions proposées, les enregistrements audio, les informations de validation et les consentements associés.</p>

<h2>2. Compte facultatif</h2>
<p>La création d’un compte n’est pas nécessaire pour répondre au questionnaire. Un compte peut être créé ultérieurement afin de retrouver un historique, consulter des statistiques ou continuer à contribuer depuis un profil identifié.</p>

<h2>3. Pourquoi ces données sont utilisées</h2>
<p>Les données servent à documenter les usages du San, à constituer un corpus contrôlé, à permettre la transcription et la validation humaine et, lorsque le consentement le permet, à préparer de futurs travaux de traduction automatique et d’apprentissage de la langue.</p>

<h2>4. Enregistrements audio</h2>
<p>Les fichiers audio ne sont pas placés dans le dossier public du site. Ils sont prévus pour être stockés dans un espace privé et accessibles uniquement par les mécanismes autorisés de l’application. La publication éventuelle d’un audio doit être distinguée de son utilisation pour transcription ou entraînement.</p>

<h2>5. Variétés linguistiques</h2>
<p>Le contributeur n’est pas obligé de choisir une étiquette technique telle que Maka, Matya ou Maya. La localité déclarée sert de contexte. L’identification linguistique finale est réalisée au cours de la validation et ne doit pas être déduite automatiquement de la seule localité.</p>

<h2>6. Minimisation et export</h2>
<p>Les exports destinés au Machine Learning doivent contenir uniquement les informations nécessaires au corpus. Les identifiants personnels et les informations de connexion ne doivent pas être intégrés directement au dataset d’entraînement.</p>

<h2>7. Évolution de cette politique</h2>
<p>Le projet étant encore en phase de développement, cette politique pourra évoluer avec le système de consentement, les modalités de publication du dataset et les mécanismes de correction ou de retrait. Toute version utilisée pour la collecte devra être identifiable et conservée dans le système de consentement.</p>
@endsection

@extends('public.legal-layout')

@section('title', 'Confidentialité')
@section('description', 'Politique de confidentialité du collecteur Langue SAN.')
@section('heading', 'Politique de confidentialité')

@section('content')
<p class="text-muted">Version 1.1 — septembre 2026. Cette page décrit les règles actuellement appliquées par le collecteur Langue SAN.</p>

<h2>1. Données collectées</h2>
<p>Il est possible de contribuer sans créer de compte. Dans ce cas, le système crée un profil pseudonymisé et associe la session à un jeton conservé dans le navigateur. Seul le hash de ce jeton est stocké côté serveur.</p>
<p>Selon le parcours, nous pouvons enregistrer la localité déclarée, le niveau de pratique du San, la capacité à l’écrire, les traductions proposées, les enregistrements audio, les informations de validation et les consentements associés.</p>

<h2>2. Compte facultatif</h2>
<p>La création d’un compte n’est pas nécessaire pour commencer à contribuer. Un compte permet ensuite de retrouver son historique, gérer son profil et exercer plus facilement ses choix concernant ses données.</p>

<h2>3. Pourquoi ces données sont utilisées</h2>
<p>Les données servent à documenter les usages du San, à constituer un corpus contrôlé, à permettre la transcription et la validation humaine et, lorsque le consentement le permet, à préparer de futurs travaux de traduction automatique et d’apprentissage de la langue.</p>

<h2>4. Enregistrements audio</h2>
<p>Les fichiers audio sont stockés hors de l’espace public du site et ne sont servis que par des routes protégées. Leur utilisation pour transcription ou validation ne vaut pas autorisation de publication publique.</p>
<p><strong>Un audio brut n’est jamais publié publiquement par défaut.</strong> Une éventuelle publication d’un audio nécessitera une autorisation spécifique distincte.</p>

<h2>5. Durées de conservation opérationnelles</h2>
<ul>
    <li><strong>Audio d’une contribution définitivement rejetée :</strong> suppression automatique après 90 jours.</li>
    <li><strong>Audio d’une contribution encore en attente sans traitement :</strong> suppression après 12 mois d’inactivité.</li>
    <li><strong>Contribution approuvée :</strong> les données nécessaires au corpus peuvent être conservées tant que le consentement applicable reste valable et que le projet en a besoin pour la traçabilité et la reproductibilité.</li>
    <li><strong>Données de compte :</strong> conservées tant que le compte est actif ou jusqu’à une demande d’anonymisation/suppression, sous réserve des traces techniques minimales nécessaires à l’intégrité du projet.</li>
</ul>

<h2>6. Correction, suppression et retrait</h2>
<p>Un contributeur connecté peut utiliser la page <strong>Mes données</strong> pour demander une correction, supprimer un enregistrement audio, retirer une contribution ou demander l’anonymisation de ses données de compte.</p>
<p>Le retrait d’une contribution est appliqué immédiatement dans le collecteur : le contenu linguistique encore stocké, ses segments, ses validations et son audio sont supprimés, la contribution est marquée comme retirée et elle est exclue de tous les futurs exports dataset.</p>
<p>Les demandes nécessitant une intervention humaine, comme une correction ou une anonymisation de compte, ont un objectif opérationnel de traitement sous 30 jours.</p>
<p>Si un dataset a déjà été publié et copié par des tiers avant la demande, le projet ne peut pas garantir la suppression de toutes les copies externes. En revanche, les versions futures publiées par le projet doivent exclure les données retirées.</p>

@auth
    @if(auth()->user()->isContributor())
        <p><a class="btn btn-primary" href="{{ route('contributor.data-requests.index') }}"><i class="bi bi-shield-check me-1"></i>Gérer mes données</a></p>
    @endif
@endauth

<h2>7. Variétés linguistiques</h2>
<p>Le contributeur n’est pas obligé de choisir une étiquette technique telle que Maka, Matya ou Maya. La localité déclarée sert de contexte. L’identification linguistique finale est réalisée au cours de la validation et ne doit pas être déduite automatiquement de la seule localité.</p>

<h2>8. Minimisation et export</h2>
<p>Les exports destinés au Machine Learning contiennent uniquement les informations nécessaires au corpus. Les identifiants personnels et les informations de connexion ne sont pas intégrés directement au dataset d’entraînement. Une contribution marquée comme retirée est systématiquement exclue des exports.</p>

<h2>9. Version du consentement</h2>
<p>Le consentement est versionné. Une modification importante des usages, de la publication ou de la politique de conservation donne lieu à une nouvelle version afin de ne pas modifier silencieusement les conditions acceptées précédemment.</p>
@endsection

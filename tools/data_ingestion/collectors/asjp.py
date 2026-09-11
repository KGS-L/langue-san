"""Collecteur ASJP.

L'implémentation du téléchargement et de l'extraction sera ajoutée dans la
première étape d'ingestion réelle. Les sorties brutes devront être stockées
sous ``data/raw/asjp/`` et ne devront pas être commitées.
"""

from pathlib import Path


RAW_OUTPUT_DIR = Path("data/raw/asjp")
SUPPORTED_ISO_CODES = ("sbd", "stj", "sym")

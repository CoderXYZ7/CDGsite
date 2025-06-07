Animatori
- ID            |id animatore, non null, auto incrementale
- Nome          |nome animatore, non null
- Cognome       |cognome animatore, non null
- Laboratorio   |id laboratorio, se vuoto allora = 13, non null
- Fascia        |A = animatore, D = fascia D, non null
- Respomsabile  |"id responsabile, id respinsabile" lista responsabilità
- Colore        |B = blu, R = rosso, G = giallo, A = arancio, se vuoto allora = X, non null
- M,J,S         |M = mini, J = juniores, S = seniores, se vuoto allora = X, non null

Laboratori
- ID            |id laboratorio, non null auto incrementale
- Nome          |nome laboratorio, non null
- Descrizione   |descrizione

Responsabili
- ID            |id responsabilità, non null, auto incrementing
- Nome          |nome responsabilità, non null
- Descrizione   |descrizione

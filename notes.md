### Detaily implementace
* Koeficient pro každý z atributů bude nabývat hodnot z intervalu (0, 1).

* 0 odpovídá totožným atributům, 1 maximálně vzdáleným.

* "Příspěvek" koeficientů do výsledného skóre videa je po jejich vypočtení převážen podle uživatelem zvolené váhy daného
parametru.

* Uživatel zadá požadovanou hodnotu parametru a jeho váhu při převažování.

##### Délka videa
* Každé video bude obsahovat informace o délce [s]
* Koeficient bude vypočten jako rozdíl délek a normalizován do rozsahu (0, 1), kde 1 bude odpovídat videu s největším
  rozdílem délek: ((t<sub>req</sub> - t<sub>vid</sub>) / max(t<sub>vids</sub>))<sup>2</sup>.
  
##### Datum nahrání videa
* Každé video bude obsahovat datum, kdy bylo publikováno na YT. (volitelně může obsahovat i datum pořízení samotného
  videa na kameru, ale tento parametr není prakticky nikde vyplněn, takže ho ignorujeme)
* Koeficient bude vypočten z rozdílu datumů (např. převedením na UNIX timestamp) podobným postupem jako u délky videa.

##### GPS
* Některá videa budou obsahovat GPS souřadnice.
* Koeficient pro videa obsahující souřadnice bude vypočten podle 
  [Great Circle distance](https://en.wikipedia.org/wiki/Great-circle_distance#Computational_formulas).
* V případě, že video nebude mít údaj GPS vyplněný, bude "uměle" doplněn. Zde několik možností: 
  * Vyplněna bude hodnota 1 - nejhorší možná - vyhledávání tedy bude upřednostňovat videa s vyplněnými souřadnicemi.
  * Vyplněna bude hodnota 0.5 - za předpokladu, že videa jsou rozmístěna rovnoměrně daleko, budou videa bez GPS
    umístěna "někde uprostřed".
  * Hodnotu pravděpodobně stanovíme experimentálně podle získaných výsledků vyhledávání.


##### Views
* Koeficient určen rozdílem požadovaného a skutečného počtu zobrazení videí. Spočten podobně jako koeficient pro délku 
  videa.

##### Thumbs up/down
* Hodnota pro výpočet koeficientu bude dána poměrem těchto dvou hodnot (bude spadat do intervalu (0, 1)).
* Pro vypočtenou hodnotu (poměr like/dislike) se poté určí "vzdálenost", podobně jako u délky videa.

##### Editační vzdálenost - jméno autora
* Video bude moci být převáženo podle editační vzdálenosti od jména autora videa. (Editační vzdálenost od názvu videa
  už dělá, pravděpodobně, samotné YouTube při vyhledávání, my nabídneme navíc podle autora.)
* Vzdálenost bude vypočtena podle [Levenshteinovy metriky](https://en.wikipedia.org/wiki/Levenshtein_distance), následně
  normalizována podobně jako v předchozích případech.
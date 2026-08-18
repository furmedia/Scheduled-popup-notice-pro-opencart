# Ghid de utilizare - Scheduled Popup & Notice Pro 2.0

## 1. Instalare

Alege arhiva care corespunde exact versiunii magazinului. Nu instala pachetul pentru OpenCart 3 pe OpenCart 2 sau 4.

### OpenCart 2 și 3

1. Intră la **Extensii > Instalator** și încarcă arhiva `.ocmod.zip` potrivită.
2. Intră la **Extensii > Modificări** și apasă **Refresh**.
3. Intră la **Extensii > Extensii > Module**.
4. Instalează și deschide **Scheduled Popup & Notice Pro**.
5. Dacă este necesar, acordă grupului de administratori permisiunile `access` și `modify`.

### OpenCart 4

1. Încarcă arhiva potrivită din **Extensions > Installer**.
2. Deschide **Extensions > Extensions > Modules**.
3. Instalează și editează modulul.

Modulul este dezactivat la prima instalare. OpenCart 4 folosește evenimente native și nu necesită Refresh la Modificări.

## 2. Campanii multiple

Poți administra până la 50 de campanii. Fiecare are:

- nume intern, vizibil doar în administrare;
- stare activată/dezactivată;
- prioritate;
- program, conținut, design, țintire și statistici proprii.

Folosește butoanele **Adaugă**, **Clonează** și **Șterge**. Dacă sunt active mai multe campanii, cea cu prioritatea mai mare apare prima, iar următoarea este afișată după închiderea celei curente.

## 3. Programare și repetare

Configurează fusul orar, data de început și data de sfârșit. Apasă câmpul sau pictograma calendar pentru a alege vizual data și ora. Momentul de sfârșit este exclusiv: la ora exactă a expirării campania nu mai este activă.

Tipurile de repetare sunt:

- fără repetare;
- săptămânal, în aceeași zi și la aceeași oră locală;
- lunar, în aceeași zi din lună.

Pentru zilele 29, 30 și 31, lunile mai scurte folosesc ultima zi disponibilă. Poți seta și o dată până la care se repetă campania. Nu este necesar cron.

## 4. Texte separate pe limbi

Pentru fiecare limbă activă din OpenCart poți scrie separat:

- titlul popupului;
- mesajul principal;
- submesajul;
- mesajul din partea de jos;
- eticheta countdownului;
- textul butonului;
- mesajul din e-mailul comenzii.

Pe site se folosește limba vizitatorului, iar în e-mail se folosește limba comenzii. O campanie nouă primește texte inițiale editabile în fiecare dintre cele șapte limbi incluse. Titlul și mesajul principal sunt obligatorii pentru fiecare limbă activă; câmpurile opționale pot rămâne goale atunci când nu dorești să fie afișate.

## 5. Shortcoduri dinamice

Poți folosi acolade sau paranteze drepte: `{start_date}` și `[start_date]` sunt echivalente. Pune cursorul într-un câmp de text și apasă shortcode-ul dorit pentru a-l introduce automat în acel loc.

| Shortcode | Ce afișează |
|---|---|
| `{start_date}` | data de început a apariției curente |
| `{start_time}` | ora de început |
| `{end_date}` | data de sfârșit |
| `{end_time}` | ora de sfârșit |
| `{days_remaining}` | numărul de zile rămase, rotunjit în sus |
| `{hours_remaining}` | numărul de ore rămase, rotunjit în sus |
| `{countdown}` | zile:ore:minute calculate la afișare |
| `{store_name}` | numele magazinului |
| `{campaign_name}` | numele intern al campaniei |
| `{year}` | anul curent în fusul campaniei |

Exemplu:

```text
În perioada {start_date}-{end_date} nu se fac expedieri. Livrările reîncep după {end_date}.
```

La repetarea săptămânală sau lunară, datele din text se actualizează singure.

## 6. Imagine și design

Poți folosi imaginea inclusă sau poți încărca JPG, PNG ori WebP de maximum 5 MB și 20 megapixeli. Pe serverele cu GD/WebP, imaginea este redimensionată automat la maximum 1280 x 960 și salvată WebP la calitatea 82. Dacă serverul nu poate genera WebP, modulul păstrează formatul original valid. Imaginile încărcate sunt păstrate în `image/catalog/scheduled-popup-notice/`.

Ai trei stiluri de pornire: Elegant, Minimal și Bold. Apoi poți modifica accentul, fundalul, culoarea textului, culoarea butonului, culoarea și opacitatea fundalului exterior, precum și intensitatea blurului paginii.

Previzualizarea din administrare arată campania selectată fără a o publica pe site.

## 7. Countdown și buton

Countdownul arată timpul rămas până la expirarea apariției active. Eticheta lui poate fi tradusă separat.

Butonul CTA poate avea text tradus, adresă proprie și deschidere în aceeași filă sau într-o filă nouă. Sunt acceptate adrese `http`/`https` și adrese locale care încep cu `/`, `?` sau `#`.

## 8. Afișare pe categorii sau produse

Poți alege:

- toate paginile;
- numai paginile categoriilor selectate;
- numai paginile produselor selectate.

Categoriile și produsele se adaugă prin căutare autocomplete. Aceeași regulă se aplică mesajului din e-mail: cel puțin un produs din comandă trebuie să corespundă țintei.

## 9. Statistici

Modulul numără afișările, clickurile pe buton și închiderile și calculează CTR. Evenimentele sunt numărate o singură dată pe sesiune și apariție.

Se salvează doar identificatorul campaniei, tipul evenimentului, data și totalul. Nu se salvează IP, e-mail, client sau amprentă de browser. Statisticile campaniei pot fi resetate din administrare.

## 10. Mesajul din e-mailul comenzii

Mesajul este adăugat numai dacă respectiva campanie este activă, are text pentru e-mail și comanda corespunde țintei. Shortcodurile sunt completate folosind limba și numele magazinului comenzii.

Extensiile care înlocuiesc complet sistemul standard de e-mail OpenCart pot necesita o integrare separată.

## 11. Cache și actualizare

Butonul de golire cache șterge fișierele cache OpenCart și încălzește prima pagină. Nu șterge coșuri, sesiuni, clienți, comenzi sau produse.

La trecerea de la versiunea 1.x nu dezinstala modulul. Încarcă 2.0 peste el, dă Refresh la Modificări pentru OpenCart 2/3, deschide modulul și salvează. Configurația veche apare ca **Imported campaign**.

## 12. Probleme frecvente

- Popupul nu apare: verifică starea modulului, starea campaniei, fusul, datele, ținta și dacă a fost deja închis în sesiunea curentă.
- Se vede versiunea veche: dă Refresh la Modificări, golește cache-ul temei și apoi folosește instrumentul cache din modul.
- Imaginea nu apare: verifică tipul, limita de 5 MB și permisiunile directorului de imagini.
- Mesajul nu ajunge în e-mail: verifică textul în limba comenzii, perioada activă, țintirea și integrarea standard de e-mail.
- Journal: instalează pachetul dedicat Journal și reîmprospătează cache-ul Journal/OpenCart.

### Przed uruchomieniem projektu należy stworzyć bazę danych:
  php bin/console doctrine:database:create
1. Aby prawidłowo stworzyć bazę danych należy skonfigurować prawidłowe połączenie w pliku .env w katalogu projektu
Należy ustawić poniższe parametry:
```
DB_USER=hubert
DB_PASS=root
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=shop

```
gdzie DB_USER to użytkownik, który ma uprawnienia do tworzenia bazy

2. Aby stworzyć użytkownika należy:
```
CREATE USER 'appuser'@'%' IDENTIFIED BY 'mocne_haslo';
GRANT CREATE ON *.* TO 'appuser'@'%';
FLUSH PRIVILEGES;
```
!Wymagane zainstalowane php8.4-mysql `apt install php8.4-mysql`
3. Aby stworzyć bazę danych 
  `php bin/console doctrine:database:create`
4. Aby odtworzyć schemat bazy
  `php bin/console doctrine:migrations:migrate`
5. by wygenerować dane testowe
  `php bin/console doctrine:fixtures:load`
Zostaną wygenerowane losowe produkty. Produkty zostaną przypisane do kategorii. Zostanie wygenerowanych 3 adminów i 30 użytkowników.
6. Aby uruchomić serwer:
  `symfony server:start`
### Nginx proxy
Aby do aplikacji na maszynie wirtualnej można było dostać się z zewnątrz należy skonfigurować nginx
1. w `/etc/nginx/sites-available/` dodać stronę o zawartości: 
```
server {
        listen 8080;
        listen [::]:8080;
        server_name 192.168.37.5;

        location / {
                proxy_pass http://127.0.0.1:8000;
                proxy_set_header Host $http_host;
                proxy_set_header X-Forwarded-Host $http_host;
                proxy_set_header X-Forwarded-Port $server_port;
                proxy_set_header X-Real-IP $remote_addr;
                proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
                proxy_set_header X-Forwarded-Proto $scheme;

                # WebSocket/HTTP2 upgrades (na wszelki wypadek)
                proxy_http_version 1.1;
                proxy_set_header Upgrade $http_upgrade;
                proxy_set_header Connection "upgrade";

                # timeouty dev-friendly
                proxy_read_timeout 300s;
        }
}
```
Gdzie `8080` to port nasłuchiwania nginxa, `192.168.37.5` to adres ip maszyny. Url na który trzeba się dostać to `192.168.37.5:8080`. Reszty nie zmieniamy
3. Należy podlinkować stronę do `/etc/nginx/sites-enabled` a następnie zrestartować nginx
``` service nginx restart ```

### Wysyłka email
Aplikacja korzysta z linku do potwierdzenia konta użytkownika. Aby móc testowo skorzystać z tej funkcjonalności można użyć pakietu `axllent/mailpit`
1. Należy stworzyć kontener docker
```
  docker run -d --name mailpit \
    -p 1025:1025 -p 8025:8025 \
    axllent/mailpit
```
2. Aby ponownie uruchomić kontener, jeśli jest wyłączony należy użyć `docker start mailpit`
3. Wszystkie maile trafiają do kolejki, aby symfony wysłało maile w kolejce należy wykonać: `php bin/console messenger:consume async -vv`
4. Przed uruchomieniem consumenta maili należy zadbać o prawidłową komunikację, o ile porty w dockerze nie były zmieniane nie ma takiej potrzeby. W razie potrzeby konfiguracja odbywa się w .env
```
###> symfony/mailer ###
MAILER_DSN=smtp://127.0.0.1:1025
###< symfony/mailer ###
```
5. Aby odczytać wszystkie wysłane maile trzeba wejść na adres: `http://192.168.37.5:8025/`

### Konfiguracja systemu płatności
Aplikacja korzysta do wykonywania płatności z payu. 
1. Należy utworzyć konto na środowisku testowym: merch-prod.snd.payu.com
2. W pliku .env ustawiamy następujące parametry
```
PAYU_ENV=sandbox
PAYU_POS_ID="PAYU_POS_ID"
PAYU_CLIENT_ID="PAYU_CIENT_ID"
PAYU_CLIENT_SECRET="sekret"
PAYU_CONTINUE_BASE=http://192.168.37.5:8080
```
3. sekret, PAYU_CLIENT_ID oraz PAYU_POS_ID znajdziesz na stronie payu w poniższej zakładce:
   1. Płatności elektroniczne
   2. Moje sklepy
   3. Wybierz sklep i na nim kliknij punkty płatności
   4. Następnie na liście punkty płatności kliknij na punkt płatności
   5. W oknie `Klucze konfiguracji` znajdziesz dane
   ```
   Id punktu płatności (pos_id):
   Protokół OAuth - client_id :
   Protokół OAuth - client_secret:
   ```
   6. Tymi danymi należy wypełnić ustawienia w konfiguracji
   7. Nie wymienione elementy konfiguracji mogą zostać puste
   8. Ponieważ, nie chciałem bawić się w tunelowanie, które jest konieczne do przetestowania webhooków, zamiast webhooków zrobiłem przycisk sprawdź status, który pobiera informacje o tym czy płatność została zakończona powodzeniem, po wykonaniu płatności. Jeżeli płatność nie została zakończona jest przycisk kontynuuj płatność, który pozwala na dokończenie płatności. Powyższe akcje są dostępne z poziomu zamówienia na liście zamówień klienta.
### Używanie sklepu
Głównym punktem dostępowym do sklepu dla konsumentów jest adres serwera, dla maszyny wirtualnej: `192.168.37.5:8080`, który przenosi automatycznie na stronę `/products`
##### Niezalogowany użytkownik
- Może przeglądać produkty
- Dodawać produkty do koszyka
- Zalogować się
  - Zalogowanie się z produktami w koszyku sprawi, że koszyk zostanie przypisany do zalogowanego użytkownika. Jeśli zalogowany użytkownik już miał koszyk, koszyki zostaną scalone.
##### Zalogowany użytkownik
- Może wszystko co niezalogowany użytkownik
- Może składać zamówienie
- Opłacać zamówienie
- Przeglądać listę zamówień

Dodatkowym punktem dostępowym do aplikacji jest `adres_maszyny/admin` np. `192.168.37.5:8080/admin`, dostęp mają tylko użytkownicy z rolą admin np. `admin1@example.com`, hasło `admin1234`. Admin ma możliwość:
- CRUD produkty
- CRUD kategorie
- Przypisywać produkty do kategorii
- Przeglądać wszystkie zamówienia, zamówienia opłacone (takie do wysyłki), zamówienia cod (płatność przy odbiorze - do wysyłki)

# unimia+

Questa repository contiene lo sviluppo del progetto "Piattaforma per la gestione degli esami universitari" (laboratorio di basi di dati a.a. 2022/23).

## Indice

1. [Installazione](#installazione)
    * [Requisiti](#requisiti)
    * [Step 1: configurare il database](#step-1-configurare-il-database)
    * [Step 2: configurare il server php](#step-2-configurare-il-server-php)
    * [Step 3: avviare il server php](#step-3-avviare-il-server-php)
2. [La base di dati](#la-base-di-dati)
    * [Modello E-R](#modello-e-r)
    * [Modello E-R (ristrutturato)](#modello-e-r-ristrutturato)
    * [Schema relazionale](#schema-relazionale)
3. [Documentazione tecnica](#documentazione-tecnica)
4. [Licenza](#licenza)

## Installazione

Breve guida per installare, configurare e avviare l'applicazione unimia+.

```console
git clone https://github.com/davi0kprogramsthings/unimia-plus.git
```

### Requisiti

Per poter procedere è necessario aver installato:
* [Postgres 15.3](https://www.postgresql.org/) (o superiore).
* [PHP 8.2.5](https://www.php.net/) (o superiore).

### Step 1: configurare il database

```console
cd 'unimia+ SQL'
```

Crea un database vuoto:

```console
createdb unimia+
```

Importa il dump dello schema public:

```console
psql unimia+ < dump/unimia+/public.sql
```

> **Nota: questa operazione importerà anche dei dati dummy per ogni tabella del db.**

### Step 2: configurare il server php

```console
cd ../unimia+
```

Il server php è configurabile attraverso il file /config.php.


#### Configurazioni

Variabile | Tipo | Valore di default | Descrizione
:--- | :--- | :--- | :---
$POSTGRESQL_HOST | string | localhost | L'indirizzo IP del servizio postgres.
$POSTGRESQL_PORT | int | 5432 | La porta in cui è in ascolto il servizio postgres.
$POSTGRESQL_USER | string | postgres | L'utente con cui il server php si connetterà al database.
$POSTGRESQL_PASSWORD | string | 12345678 | Le credenziali necessarie per connettersi al database.
$POSTGRESQL_DBNAME | string | unimia+ | Il nome del database dedicato all'applicazione.

### Step 3: avviare il server php

L'applicazione web è avviabile attraverso il [built-in web server](https://www.php.net/manual/en/features.commandline.webserver.php) di php:

```
php -S localhost:3000
```

L'app sarà ora raggiungibile al seguente url:
```
http://localhost:3000/
```

#### Attenzione!

Il built-in web server di php è stato sviluppato per aiutare lo sviluppo di applicazioni. Può anche essere utile per scopo di test o per dimostrazioni applicative che sono eseguite in ambienti controllati. Non è destinato ad essere un web server con tutte le funzionalità. E non dovrebbe essere usato su una rete pubblica.

E' raccomandato fare uso di un web server come [Apache 2.4](https://httpd.apache.org/docs/2.4/) o [nginx](https://nginx.org/en/).

## La base di dati

### Modello E-R

![Modello E-R](https://i.imgur.com/fC2H3mc.jpeg)

### Modello E-R (ristrutturato)

![Modello E-R (ristrutturato)](https://i.imgur.com/5yDUyBr.jpeg)

### Schema relazionale

![Schema relazionale](https://i.imgur.com/QvjAwLr.jpeg)

## Documentazione tecnica

### Indice

1. [Domini](#domini)
    * [email](#email)
    * [website](#website)
    * [telephone](#telephone)
2. [Tabelle](#tabelle)
    * [secretary](#secretary)
    * [professor](#professor)
    * [student](#student)
    * [course](#course)
    * [teaching](#teaching)
    * [exam](#exam)
    * [correlations](#correlations)
    * [prerequisites](#prerequisites)
    * [career](#career)
    * [historic_student](#historic_student)
    * [historic_career](#historic_career)
3. [Viste](#viste)
    * [full_student](#full_student)
    * [full_historic_student](#full_historic_student)
    * [full_teaching](#full_teaching)
    * [full_exam](#full_exam)
    * [full_career](#full_career)
    * [full_historic_career](#full_historic_career)
    * [available_professor](#available_professor)
4. [Funzioni](#funzioni)
    * [get_teaching_correlations](#get_teaching_correlations)
    * [get_teaching_prerequisites](#get_teaching_prerequisites)
    * [get_courses](#get_courses)
    * [get_full_teachings](#get_full_teachings)
    * [get_full_exams](#get_full_exams)
    * [get_full_career](#get_full_career)
    * [get_full_historic_career](#get_full_historic_career)
    * [get_student_enrollments](#get_student_enrollments)
    * [get_exam_students](#get_exam_students)
5. [Procedure](#procedure)
    * [insert_teaching](#insert_teaching)
    * [update_teaching](#update_teaching)
6. [Trigger](#trigger)
    * [set_student_matriculation](#set_student_matriculation)
    * [check_course_years](#check_course_years)
    * [check_teaching_year](#check_teaching_year)
    * [check_teaching_professor](#check_teaching_professor)
    * [check_correlations_professor](#check_correlations_professor)
    * [check_prerequisites_requisite](#check_prerequisites_requisite)
    * [check_exam](#check_exam)
    * [check_exam_date](#check_exam_date)
    * [check_career_course](#check_career_course)
    * [check_career_mark](#check_career_mark)
    * [check_career_missing_requisites](#check_career_missing_requisites)
    * [check_career_student_already_enrolled](#check_career_student_already_enrolled)
    * [make_historic_student](#make_historic_student)
    * [populate_historic_career](#populate_historic_career)

### Domini

#### [email](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/1-domains.sql#L1)

**Descrizione:**
- **Tipo di dato:** VARCHAR(254)
- **Descrizione:** questo dominio definisce un campo di tipo VARCHAR che rappresenta un indirizzo email.
- **Vincolo di controllo:** l'indirizzo deve seguire un formato di email standard. La regex inclusa verifica che l'email abbia una struttura valida, inclusi caratteri permessi prima della chiocciola (@), un dominio valido, e eventuali sotto-domini.


#### [website](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/1-domains.sql#L9)

**Descrizione:**
- **Tipo di dato:** VARCHAR(2048)
- **Descrizione:** questo dominio definisce un campo di tipo VARCHAR che rappresenta un URL di un sito web.
- **Vincolo di controllo:** l'URL deve iniziare con http, ftp o https, seguito da un dominio valido e, opzionalmente, un percorso URL. La regex inclusa verifica che l'URL sia ben formato secondo questi criteri.


#### [telephone](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/1-domains.sql#L19)

**Descrizione:**
- **Tipo di dato:** VARCHAR(10)
- **Descrizione:** questo dominio definisce un campo di tipo VARCHAR che rappresenta un numero di telefono.
- **Vincolo di controllo:** il numero di telefono deve essere composto da 9 o 10 cifre numeriche. La regex inclusa verifica che il numero contenga solo cifre da 0 a 9, e che abbia una lunghezza di 9 o 10 caratteri.

### Tabelle

#### [secretary](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/2-tables.sql#L1)

La tabella `secretary` contiene le informazioni per i segretari dell'istituto.

#### [professor](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/2-tables.sql#L14)

La tabella `professor` contiene le informazioni dei professori dell'istituto.

#### [student](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/2-tables.sql#L30)

La tabella `student` contiene le informazioni degli studenti dell'istituto.

#### [course](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/2-tables.sql#L47)

La tabella `course` contiene le informazioni sui corsi di laurea offerti dall'istituto.

#### [teaching](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/2-tables.sql#L64)

La tabella `teaching` contiene le informazioni sugli insegnamenti.

#### [exam](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/2-tables.sql#L87)

La tabella `exam` contiene le informazioni sugli esami.

#### [correlations](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/2-tables.sql#L105)

La tabella `correlations` contiene le correlazioni tra professori e insegnamenti.

#### [prerequisites](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/2-tables.sql#L123)

La tabella `prerequisites` contiene i prerequisiti degli insegnamenti.

#### [career](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/2-tables.sql#L145)

La tabella `career` contiene le informazioni sulla carriera degli studenti.

#### [historic_student](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/2-tables.sql#L177)

La tabella `historic_student` contiene le informazioni degli studenti cancellati.

#### [historic_career](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/2-tables.sql#L194)

La tabella `historic_career` contiene le informazioni sulla carriera degli studenti cancellati.

### Viste

#### [full_student](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/3-views.sql#L1)

La vista `full_student` fornisce informazioni complete sugli studenti, incluse le informazioni sul corso di laurea a cui sono iscritti.

#### [full_historic_student](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/3-views.sql#L15)

La vista `full_historic_student` fornisce informazioni complete sugli studenti storici, incluse le informazioni sul corso di laurea a cui erano iscritti.

#### [full_teaching](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/3-views.sql#L29)

La vista `full_teaching` fornisce informazioni complete sugli insegnamenti, incluse le informazioni sul professore che tiene l'insegnamento e sul corso di laurea associato.

#### [full_exam](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/3-views.sql#L47)

La vista `full_exam` fornisce informazioni complete sugli esami, incluse le informazioni sull'insegnamento associato, sul professore che tiene l'insegnamento e sul corso di laurea associato.

#### [full_career](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/3-views.sql#L68)

La vista `full_career` fornisce informazioni complete sulla carriera degli studenti, incluse le informazioni sull'esame svolto, sull'insegnamento associato, sul professore che tiene l'insegnamento e sul corso di laurea associato.

#### [full_historic_career](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/3-views.sql#L94)

La vista `full_historic_career` fornisce informazioni complete sulla carriera degli studenti storici, incluse le informazioni sull'esame svolto, sull'insegnamento associato, sul professore che tiene l'insegnamento e sul corso di laurea associato.

#### [available_professor](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/3-views.sql#L119)

La vista `available_professor` fornisce un elenco di professori disponibili, ovvero quelli che hanno meno di tre insegnamenti assegnati.

### Funzioni

#### [get_teaching_correlations](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/4-functions.sql#L1)

La funzione `get_teaching_correlations` restituisce una lista di professori che sono correlati a un determinato insegnamento.

**Parametri:**
- `teaching.course`: Il codice del corso dell'insegnamento.
- `teaching.identifier`: L'identificatore dell'insegnamento.

#### [get_teaching_prerequisites](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/4-functions.sql#L18)

La funzione `get_teaching_prerequisites` restituisce una lista di insegnamenti prerequisiti per un determinato insegnamento.

**Parametri:**
- `teaching.course`: Il codice del corso dell'insegnamento.
- `teaching.identifier`: L'identificatore dell'insegnamento.

#### [get_courses](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/4-functions.sql#L35)

La funzione `get_courses` restituisce una lista di corsi associati a un determinato professore.

**Parametri:**
- `professor.email`: L'email del professore.

#### [get_full_teachings](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/4-functions.sql#L50)

La funzione `get_full_teachings` restituisce una lista completa di insegnamenti tenuti da un determinato professore.

**Parametri:**
- `professor.email`: L'email del professore.

#### [get_full_exams](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/4-functions.sql#L64)

La funzione `get_full_exams` restituisce una lista completa di esami per cui un determinato professore è responsabile.

**Parametri:**
- `professor.email`: L'email del professore.

#### [get_full_career](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/4-functions.sql#L78)

La funzione `get_full_career` restituisce una lista completa della carriera accademica di uno studente.

**Parametri:**
- `student.email`: L'email dello studente.

#### [get_full_historic_career](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/4-functions.sql#L92)

La funzione `get_full_historic_career` restituisce una lista completa della carriera accademica storica di uno studente.

**Parametri:**
- `historic_student.matriculation`: Il numero di matricola dello studente storico.

#### [get_student_enrollments](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/4-functions.sql#L106)

La funzione `get_student_enrollments` restituisce una lista completa degli esami a cui uno studente è iscritto ma non ancora svolto.

**Parametri:**
- `student.email`: L'email dello studente.

#### [get_exam_students](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/4-functions.sql#L121)

La funzione `get_exam_students` restituisce una lista completa degli studenti iscritti a un esame specifico.

**Parametri:**
- `exam.course`: Il codice del corso dell'esame.
- `exam.identifier`: L'identificatore dell'esame.
- `exam.date`: La data dell'esame.

### Procedure

#### [insert_teaching](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/5-procedures.sql#L1)

La procedura `insert_teaching` consente di inserire un nuovo insegnamento nel database, insieme alle relative correlazioni e prerequisiti.

**Parametri:**
- `_teaching`: Un oggetto di tipo `teaching` contenente le informazioni sull'insegnamento da inserire.
- `_correlations`: Un array di indirizzi email (`EMAIL[]`) rappresentante i professori correlati all'insegnamento.
- `_prerequisites`: Un array di codici di insegnamento (`CHAR(3)[]`) rappresentante i prerequisiti dell'insegnamento.

#### [update_teaching](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/5-procedures.sql#L28)

La procedura `update_teaching` consente di aggiornare le informazioni di un insegnamento nel database, insieme alle relative correlazioni e prerequisiti.

**Parametri:**
- `_course`: Il codice del corso dell'insegnamento da aggiornare.
- `_identifier`: L'identificatore dell'insegnamento da aggiornare.
- `_teaching`: Un oggetto di tipo `teaching` contenente le nuove informazioni sull'insegnamento.
- `_correlations`: Un array di indirizzi email (`EMAIL[]`) rappresentante i professori correlati all'insegnamento.
- `_prerequisites`: Un array di codici di insegnamento (`CHAR(3)[]`) rappresentante i prerequisiti dell'insegnamento.

### Trigger

#### [set_student_matriculation](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/6-triggers.sql#L1)

Il trigger imposta automaticamente il numero di matricola di uno studente se non è stato fornito durante l'inserimento. Se il numero di matricola è nullo, viene generato automaticamente un nuovo numero di matricola in base al massimo numero di matricola presente nella tabella `student` e nella tabella `historic_student`.

#### [check_course_years](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/6-triggers.sql#L44)

Il trigger verifica se ci sono insegnamenti associati a un corso di laurea con un numero di anni superiore al valore della colonna `years` durante un aggiornamento della tabella `course`.

#### [check_teaching_year](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/6-triggers.sql#L68)

Il trigger controlla che il valore fornito per l'anno di insegnamento sia compreso tra 1 e il numero di anni del corso di laurea corrispondente.

#### [check_teaching_professor](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/6-triggers.sql#L93)

Il trigger controlla che un professore non abbia più di 3 insegnamenti associati contemporaneamente.

#### [check_correlations_professor](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/6-triggers.sql#L120)

Il trigger controlla che un professore non sia già associato all'insegnamento correlato.

#### [check_prerequisites_requisite](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/6-triggers.sql#L145)

Il trigger controlla che i prerequisiti di un insegnamento precedano effettivamente l'insegnamento nel programma del corso di laurea.

#### [check_exam](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/6-triggers.sql#L176)

Il trigger controlla che non sia possibile fissare più di un esame nella stessa data per insegnamenti diversi dello stesso corso di laurea e anno.

#### [check_exam_date](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/6-triggers.sql#L210)

Il trigger impedisce di fissare un esame entro i 7 giorni successivi alla data odierna.

#### [check_career_course](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/6-triggers.sql#L229)

Il trigger controlla che uno studente possa iscriversi solo a esami di insegnamenti del suo corso di laurea.

#### [check_career_mark](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/6-triggers.sql#L252)

Il trigger verifica se uno studente ha già una valutazione sufficiente per l'insegnamento al quale si sta iscrivendo.

#### [check_career_missing_requisites](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/6-triggers.sql#L282)

Il trigger controlla se lo studente rispetta tutti i prerequisiti necessari per iscriversi all'esame di un determinato insegnamento.

#### [check_career_student_already_enrolled](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/6-triggers.sql#L312)

Il trigger verifica se lo studente è già iscritto a un esame per lo stesso insegnamento.

#### [make_historic_student](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/6-triggers.sql#L341)

Il trigger copia i dati dello studente eliminato nella tabella `historic_student`.

#### [populate_historic_career](https://github.com/davi0kprogramsthings/unimia-plus/blob/master/unimia%2B%20SQL/sql/6-triggers.sql#L361)

Il trigger trasferisce la carriera di uno studente appena eliminato nella tabella `historic_career`.

## Licenza

```
Copyright (c) 2024 Davide Casale

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
```
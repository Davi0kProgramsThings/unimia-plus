<?php
    require('../../scripts/init.php');

    require_role('professor');

    $email = $_SESSION['user']['email'];

    $course = $_GET['corso'];

    $identifier = $_GET['insegnamento'];

    $date = $_GET['esame'];

    [ $rows, $_ ] = execute_query(
        'SELECT * FROM get_full_exams($1) WHERE course=$2 AND identifier=$3 AND date=$4', 
            [ $email, $course, $identifier, $date ]);

    $exam = $rows[0];

    if (!isset($exam))
        redirect('/docenti/visualizza/esami.php');

    [ $teachings, $_ ] = execute_query(
        'SELECT * FROM get_full_teachings($1) ORDER BY name', 
            [ $email ]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        [ $teaching_course, $teaching_identifier ] = explode(',', $_POST['teaching'] );

        $query = "
            UPDATE exam 
            SET course=$1,
                identifier=$2,
                date=$3,
                time=$4,
                place=$5
            WHERE course=$6 AND 
                  identifier=$7 AND
                  date=$8
        ";

        [ $result, $err ] = execute_query($query, [
            $teaching_course,
            $teaching_identifier,
            $_POST['date'],
            !empty($_POST['time']) ? $_POST['time'] : null,
            !empty($_POST['place']) ? $_POST['place'] : null,

            $course,
            $identifier,
            $date
        ]);

        if (isset($result)) {
            redirect("/docenti/visualizza/esami.php?corso=$teaching_course&insegnamento=$teaching_identifier");
        }

        if (isset($err)) {
            $error = parse_error_message($err, [
                "exam_pkey" => [ "field" => "date", "message" => "Hai già fissato un esame per questo insegnamento in questa data." ],
                "check_exam_date" => [ "field" => "date", "message" => "La data dell'esame deve essere fissata almeno 7 giorni dopo la data odierna." ],
                "check_exam" => [ "field" => "date", "message" => "Hai già fissato un esame per un altro insegnamento dello stesso corso di laurea e anno in questa data." ],
            ]);
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('../../components/head.php') ?>

    <title>Unimia+ | Docenti | Gestisci | Esami</title>
</head>

<body data-theme="light">
    <?php require('../../components/docenti/navbar.php') ?>

    <div class="columns is-centered p-5">
        <div class="column is-two-fifths">
            <div class="box">
                <form action="" method="post">
                    <div class="columns">
                        <div class="column is-four-fifths">
                            <span class="icon-text">
                                <span class="icon is-large">
                                    <i class="fa-solid fa-calendar-days fa-2xl"></i>
                                </span>

                                <h1 class="title mt-2">Gestisci esame</h1>
                            </span>
                        </div>

                        <div class="column has-text-right">
                            <a class="button pl-5" onclick="history.back()">
                                <span class="icon-text">
                                    <span class="icon">
                                        <i class="fa-solid fa-circle-left fa-sm mr-3"></i>
                                    </span>

                                    Indietro
                                </span>
                            </a>
                        </div>
                    </div>

                    <div class="field mt-3">
                        <label class="label">Insegnamento <span class="has-text-danger">*</span></label>
                        
                        <div class="control has-icons-left">
                            <div class="select is-fullwidth">
                                <select name="teaching">
                                    <?php foreach ($teachings as $teaching): ?>
                                        <option 
                                            value="<?= $teaching['course'] . ',' . $teaching['identifier'] ?>" 
                                            
                                            <?= (($teaching['course'] . ',' . $teaching['identifier']) == ($_POST['teaching'] ?? ($exam['course'] . ',' . $exam['identifier']))) ? 'selected' : '' ?>
                                        >
                                            <?= $teaching['course_class'] ?> | <?= $teaching['course'] ?> | <?= $teaching['course_title'] ?> | <?= $teaching['name'] ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="icon is-small is-left">
                                <i class="fa-solid fa-book"></i>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Data <span class="has-text-danger">*</span></label>

                        <p class="control has-icons-left">
                            <input class="input" type="date" name="date" 
                                value="<?= $_POST['date'] ?? $exam['date'] ?>" 
                                required/>

                            <span class="icon is-small is-left">
                                <i class="fa-solid fa-calendar"></i>
                            </span>
                        </p>

                        <?php if ($error['field'] === 'date'): ?>
                            <p class="help is-danger"><?= $error['message'] ?></p>
                        <?php endif ?>
                    </div>

                    <div class="field mt-3">
                        <label class="label">Ora</label>

                        <p class="control has-icons-left">
                            <input class="input" type="text" name="time" 
                                placeholder="15:30" 
                                value="<?= $_POST['time'] ?? $exam['time'] ?>"/>

                            <span class="icon is-small is-left">
                                <i class="fa-solid fa-clock"></i>
                            </span>
                        </p>
                    </div>

                    <div class="field mt-3">
                        <label class="label">Luogo</label>

                        <p class="control has-icons-left">
                            <input class="input" type="text" name="place" 
                                placeholder="Dipartimento di informatica" 
                                value="<?= $_POST['place'] ?? $exam['place'] ?>"/>

                            <span class="icon is-small is-left">
                                <i class="fa-solid fa-location-dot"></i>
                            </span>
                        </p>
                    </div>

                    <div class="field mt-5">
                        <p class="control">
                            <button class="button is-link is-fullwidth" type="submit">
                                <span class="icon-text">
                                    Aggiorna esame

                                    <span class="icon">
                                        <i class="fa-solid fa-pen ml-3"></i>
                                    </span>
                                </span>
                            </button>
                        </p>
                    </div>
                </form>
            </div>

            <?php if ($error === false): ?>
                <div class="notification is-danger is-light mt-3">
                    <strong>Errore non previsto: riprovare più tardi...</strong>
                </div>
            <?php endif ?>
        </div>
    </div>
</body>

</html>

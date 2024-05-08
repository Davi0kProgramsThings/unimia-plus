<?php
    require('../../scripts/init.php');

    require_role('secretary');

    [ $courses, $_ ] = execute_query('SELECT * FROM course ORDER BY title');

    [ $professors, $_ ] = execute_query('SELECT * FROM available_professor ORDER BY surname');

    [ $teachings, $_ ] = execute_query('SELECT * FROM full_teaching ORDER BY name');

    function insert_teaching() {
        $prerequisites = [ ];

        foreach (($_POST['prerequisites'] ?? [ ]) as $prerequisite) {
            [ $prerequisite_course, $prerequisite_identifier ] = explode(',', $prerequisite);

            if ($_POST['course'] === $prerequisite_course)
                $prerequisites[] = $prerequisite_identifier;
            else
                return [ 
                    "field" => "prerequisites", 
                    
                    "message" => "Gli insegnamenti propedeutici non possono appartenere ad altri corsi di laurea." 
                ];
        }

        $query = 'CALL insert_teaching(($1, $2, $3, $4, $5, $6, $7, $8), $9, $10)';

        [ $result, $err ] = execute_query($query, [
            $_POST['course'],
            $_POST['identifier'],
            $_POST['name'],
            $_POST['description'],
            $_POST['year'],
            $_POST['semester'],
            $_POST['credits'],
            $_POST['professor'],
            '{' . implode(',', $_POST['correlations'] ?? [ ]) . '}',
            '{' . implode(',', $prerequisites) . '}'
        ]);

        if (isset($result)) {
            redirect("/segreteria/visualizza/insegnamenti.php?corso={$_POST['course']}");
        }

        if (isset($err)) {
            return parse_error_message($err, [
                "teaching_pkey" => [ "field" => "identifier", "message" => "Nel corso di laurea selezionato esiste già un insegnamento con lo stesso identificatore." ],
                "check_teaching_year" => [ "field" => "year", "message" => "Anno non valido." ],
                "check_correlations_professor" => [ "field" => "correlations", "message" => "Il professore di ruolo non può apparire in questa lista." ],
                "check_prerequisites_requisite" => [ "field" => "prerequisites", "message" => "Le propedeuticità devono precedere l'insegnamento nel programma del corso di laurea." ]
            ]);
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $error = insert_teaching();
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('../../components/head.php') ?>

    <title>Unimia+ | Segreteria | Crea | Insegnamenti</title>
</head>

<body data-theme="light">
    <?php require('../../components/segreteria/navbar.php') ?>

    <div class="columns is-centered p-5">
        <div class="column is-two-fifths">
            <form class="box" action="" method="post">
                <div class="columns">
                    <div class="column is-four-fifths">
                        <span class="icon-text">
                            <span class="icon is-large">
                                <i class="fa-solid fa-book fa-2xl"></i>
                            </span>

                            <h1 class="title mt-2">Crea insegnamento</h1>
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
                    <label class="label">Corso di laurea <span class="has-text-danger">*</span></label>
                    
                    <div class="control has-icons-left">
                        <div class="select is-fullwidth">
                            <select name="course">
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['code'] ?>" <?= ($course['code'] == ($_POST['course'] ?? $_GET['corso'])) ? 'selected' : '' ?>>
                                        <?= $course['class'] ?> | <?= $course['code'] ?> | <?= $course['title'] ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="icon is-small is-left">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                    </div>
                </div>

                <div class="field mt-3">
                    <label class="label">Identificatore <span class="has-text-danger">*</span></label>

                    <p class="control has-icons-left">
                        <input class="input" type="text" name="identifier" 
                            placeholder="0A-" 
                            value="<?= $_POST['identifier'] ?>"
                            minlength="3"
                            maxlength="3"
                            required/>

                        <span class="icon is-small is-left">
                            <i class="fa-solid fa-tag"></i>
                        </span>
                    </p>

                    <?php if ($error['field'] === 'identifier'): ?>
                        <p class="help is-danger"><?= $error['message'] ?></p>
                    <?php endif ?>
                </div>

                <div class="field mt-3">
                    <label class="label">Nome <span class="has-text-danger">*</span></label>

                    <p class="control has-icons-left">
                        <input class="input" type="text" name="name" 
                            placeholder="Algebra lineare e geometria analitica" 
                            value="<?= $_POST['name'] ?>"
                            maxlength="50"
                            required/>

                        <span class="icon is-small is-left">
                            <i class="fa-solid fa-diamond"></i>
                        </span>
                    </p>
                </div>

                <div class="field mt-3">
                    <label class="label">Descrizione <span class="has-text-danger">*</span></label>

                    <p class="control">
                        <textarea 
                            class="textarea"
                            name="description" 
                            rows="3"
                            required
                        ><?= $_POST['description'] ?></textarea>
                    </p>
                </div>

                <div class="mt-3">
                    <div class="columns">
                        <div class="column field">
                            <label class="label">Anno <span class="has-text-danger">*</span></label>
                            
                            <div class="control has-icons-left">
                                <div class="select is-fullwidth">
                                    <select name="year">
                                        <?php for ($i = 1; $i <= 3; $i++): ?>
                                            <option <?= ($i == $_POST['year']) ? 'selected' : '' ?>>
                                                <?= $i ?>
                                            </option>
                                        <?php endfor ?>
                                    </select>
                                </div>

                                <div class="icon is-small is-left">
                                    <i class="fa-solid fa-calendar"></i>
                                </div>
                            </div>

                            <?php if ($error['field'] === 'year'): ?>
                                <p class="help is-danger"><?= $error['message'] ?></p>
                            <?php endif ?>
                        </div>

                        <div class="column field">
                            <label class="label">Semestre <span class="has-text-danger">*</span></label>
                            
                            <div class="control has-icons-left">
                                <div class="select is-fullwidth">
                                    <select name="semester">
                                        <?php for ($i = 1; $i <= 2; $i++): ?>
                                            <option <?= ($i == $_POST['semester']) ? 'selected' : '' ?>>
                                                <?= $i ?>
                                            </option>
                                        <?php endfor ?>
                                    </select>
                                </div>

                                <div class="icon is-small is-left">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </div>
                            </div>
                        </div>

                        <div class="column field">
                            <label class="label">Crediti <span class="has-text-danger">*</span></label>
                            
                            <div class="control has-icons-left">
                                <div class="select is-fullwidth">
                                    <select name="credits">
                                        <?php for ($i = 3; $i <= 15; $i += 3): ?>
                                            <option <?= ($i == $_POST['credits']) ? 'selected' : '' ?>>
                                                <?= $i ?>
                                            </option>
                                        <?php endfor ?>
                                    </select>
                                </div>

                                <div class="icon is-small is-left">
                                    <i class="fa-solid fa-trophy"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Professore <span class="has-text-danger">*</span></label>
                    
                    <div class="control has-icons-left">
                        <div class="select is-fullwidth">
                            <select name="professor">
                                <?php foreach ($professors as $professor): ?>
                                    <option value="<?= $professor['email'] ?>" <?= ($professor['email'] == $_POST['professor']) ? 'selected' : '' ?>>
                                        <?= $professor['name'] ?> <?= $professor['surname'] ?> (<?= $professor['email'] ?>)
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="icon is-small is-left">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Altri professori</label>
                    
                    <div class="control has-icons-left">
                        <div class="select is-multiple is-fullwidth">
                            <select name="correlations[]" multiple size="5">
                                <?php foreach ($professors as $professor): ?>
                                    <option value="<?= $professor['email'] ?>" <?= in_array($professor['email'], $_POST['correlations'] ?? [ ]) ? 'selected' : '' ?>>
                                        <?= $professor['name'] ?> <?= $professor['surname'] ?> (<?= $professor['email'] ?>)
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="icon is-small is-left">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                    </div>

                    <?php if ($error['field'] === 'correlations'): ?>
                        <p class="help is-danger"><?= $error['message'] ?></p>
                    <?php endif ?>
                </div>

                <div class="field">
                    <label class="label">Propedeuticità</label>
                    
                    <div class="control has-icons-left">
                        <div class="select is-multiple is-fullwidth">
                            <select name="prerequisites[]" multiple size="5">
                                <?php foreach ($teachings as $teaching): ?>
                                    <option 
                                        value="<?= $teaching['course'] . ',' . $teaching['identifier'] ?>"
                                        
                                        <?= in_array($teaching['course'] . ',' . $teaching['identifier'], $_POST['prerequisites'] ?? [ ]) ? 'selected' : '' ?>
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

                    <?php if ($error['field'] === 'prerequisites'): ?>
                        <p class="help is-danger"><?= $error['message'] ?></p>
                    <?php endif ?>
                </div>

                <div class="field mt-5">
                    <p class="control">
                        <button class="button is-link is-fullwidth" type="submit">
                            Crea insegnamento +
                        </button>
                    </p>
                </div>
            </form>

            <?php if ($error === false): ?>
                <div class="notification is-danger is-light mt-3">
                    <strong>Errore non previsto: riprovare più tardi...</strong>
                </div>
            <?php endif ?>
        </div>
    </div>
</body>

</html>

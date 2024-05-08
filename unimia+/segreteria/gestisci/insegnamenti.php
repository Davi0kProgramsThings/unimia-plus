<?php
    require('../../scripts/init.php');

    require_role('secretary');

    $course = $_GET['corso'];

    $identifier = $_GET['insegnamento'];

    [ $rows, $_ ]= execute_query(
        'SELECT * FROM full_teaching WHERE course=$1 AND identifier=$2', 
            [ $course, $identifier ]);

    $teaching = $rows[0];

    if (!isset($teaching))
        redirect('/segreteria/visualizza/insegnamenti.php');

    [ $courses, $_ ] = execute_query('SELECT * FROM course ORDER BY title');

    [ $professors, $_ ] = execute_query('SELECT * FROM available_professor ORDER BY surname');
    
    [ $teachings, $_ ] = execute_query(
        'SELECT * FROM full_teaching WHERE course=$1 AND identifier<>$2 ORDER BY name', 
            [ $course, $identifier ]
    );

    $teaching_correlations = array_map(
        fn ($correlation) => $correlation['email'],
            execute_query('SELECT * FROM get_teaching_correlations($1, $2)', [ $course, $identifier ])[0]);

    $teaching_prerequisites = array_map(
        fn ($prerequisite) => $prerequisite['course'] . ',' . $prerequisite['identifier'],
            execute_query('SELECT * FROM get_teaching_prerequisites($1, $2)', [ $course, $identifier ])[0]);

    function update_teaching($course, $identifier) {
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

        $query = 'CALL update_teaching($1, $2, ($3, $4, $5, $6, $7, $8, $9, $10), $11, $12)';

        [ $result, $err ] = execute_query($query, [
            $course,
            $identifier,
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
            redirect("/segreteria/visualizza/insegnamenti.php?corso=$course");
        }

        if (isset($err)) {
            return parse_error_message($err, [
                "teaching_pkey" => [ "field" => "identifier", "message" => "Nel corso di laurea selezionato esiste già un insegnamento con lo stesso identificatore." ],
                "check_teaching_year" => [ "field" => "year", "message" => "Anno non valido." ],
                "check_correlations_professor" => [ "field" => "correlations", "message" => "Il professore di ruolo non può apparire in questa lista." ],
                "check_prerequisites_requisite" => [ "field" => "prerequisites", "message" => "Le propedeuticità devono precedere l'insegnamento nel programma del corso di laurea." ],
                "check_career_course" => [ "field" => "course", "message" => "E' possibile cambiare il corso di laurea solo se non ci sono studenti iscritti a esami di questo insegnamento." ]
            ]);
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $error = update_teaching($course, $identifier);
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('../../components/head.php') ?>

    <title>Unimia+ | Segreteria | Gestisci | Insegnamenti</title>
</head>

<body data-theme="light">
    <?php require('../../components/segreteria/navbar.php') ?>

    <div class="columns is-centered p-5">
        <div class="column is-two-fifths">
            <div class="box">
                <form action="" method="post">
                    <div class="columns">
                        <div class="column is-four-fifths">
                            <span class="icon-text">
                                <span class="icon is-large">
                                    <i class="fa-solid fa-book fa-2xl"></i>
                                </span>

                                <h1 class="title mt-2">Gestisci insegnamento</h1>
                            </span>
                        </div>

                        <div class="column has-text-right">
                            <button class="button pl-5" onclick="history.back()">
                                <span class="icon-text">
                                    <span class="icon">
                                        <i class="fa-solid fa-circle-left fa-sm mr-3"></i>
                                    </span>

                                    Indietro
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="field mt-3">
                        <label class="label">Corso di laurea <span class="has-text-danger">*</span></label>
                        
                        <div class="control has-icons-left">
                            <div class="select is-fullwidth">
                                <select name="course">
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?= $course['code'] ?>" <?= ($course['code'] == ($_POST['course'] ?? $teaching['course'])) ? 'selected' : '' ?>>
                                            <?= $course['class'] ?> | <?= $course['code'] ?> | <?= $course['title'] ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="icon is-small is-left">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                        </div>

                        <?php if ($error['field'] === 'course'): ?>
                            <p class="help is-danger"><?= $error['message'] ?></p>
                        <?php endif ?>
                    </div>

                    <div class="field mt-3">
                        <label class="label">Identificatore <span class="has-text-danger">*</span></label>

                        <p class="control has-icons-left">
                            <input class="input" type="text" name="identifier" 
                                placeholder="0A-" 
                                value="<?= $_POST['identifier'] ?? $teaching['identifier'] ?>"
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
                                value="<?= $_POST['name'] ?? $teaching['name'] ?>"
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
                            ><?= $_POST['description'] ?? $teaching['description'] ?></textarea>
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
                                                <option <?= ($i == ($_POST['year'] ?? $teaching['year'])) ? 'selected' : '' ?>>
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
                                                <option <?= ($i == ($_POST['semester'] ?? $teaching['semester'])) ? 'selected' : '' ?>>
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
                                                <option <?= ($i == ($_POST['credits'] ?? $teaching['credits'])) ? 'selected' : '' ?>>
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
                                        <option value="<?= $professor['email'] ?>" <?= ($professor['email'] == ($_POST['professor'] ?? $teaching['professor'])) ? 'selected' : '' ?>>
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
                                        <option value="<?= $professor['email'] ?>" <?= in_array($professor['email'], ($_POST['correlations'] ?? $teaching_correlations)) ? 'selected' : '' ?>>
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
                                            
                                            <?= in_array($teaching['course'] . ',' . $teaching['identifier'], ($_POST['prerequisites'] ?? $teaching_prerequisites)) ? 'selected' : '' ?>
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
                                <span class="icon-text">
                                    Aggiorna insegnamento

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

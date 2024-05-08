--#region full_student

CREATE OR REPLACE VIEW full_student AS (
    SELECT 
        _s.*,

        _c.class course_class,
        _c.title course_title
    FROM student _s
    JOIN course _c ON _s.course = _c.code
);

--#endregion

--#region full_historic_student

CREATE OR REPLACE VIEW full_historic_student AS (
    SELECT 
        _s.*,

        _c.class course_class,
        _c.title course_title
    FROM historic_student _s
    JOIN course _c ON _s.course = _c.code
);

--#endregion

--#region full_teaching

CREATE OR REPLACE VIEW full_teaching AS (
    SELECT
        _t.*, 

        _p.name professor_name,
        _p.surname professor_surname,

        _c.class course_class,
        _c.title course_title
    FROM teaching _t
    JOIN course _c ON _t.course = _c.code
    JOIN professor _p ON _t.professor = _p.email
);

--#endregion

--#region full_exam

CREATE OR REPLACE VIEW full_exam AS (
    SELECT 
		_e.*,

        _t.name teaching_name,
        _t.credits teaching_credits,
        _t.professor teaching_professor,

        _t.professor_name,
        _t.professor_surname,

        _t.course_class,
        _t.course_title
	FROM exam _e
    JOIN full_teaching _t ON _e.course = _t.course AND _e.identifier = _t.identifier
);

--#endregion

--#region full_career

CREATE OR REPLACE VIEW full_career AS (
    SELECT 
		_c.*,

        _s.name student_name,
        _s.surname student_surname,
        _s.matriculation student_matriculation,
	
        _e.teaching_name,
        _e.teaching_credits,
        _e.teaching_professor,

        _e.professor_name,
        _e.professor_surname,

        _e.course_class,
        _e.course_title
	FROM career _c
    JOIN student _s ON _c.student = _s.email
    JOIN full_exam _e ON _c.course = _e.course AND _c.identifier = _e.identifier AND _c.date = _e.date
);

--#endregion

--#region full_historic_career

CREATE OR REPLACE VIEW full_historic_career AS (
    SELECT 
		_c.*,

        _s.name student_name,
        _s.surname student_surname,
	
        _e.teaching_name,
        _e.teaching_credits,
        _e.teaching_professor,

        _e.professor_name,
        _e.professor_surname,

        _e.course_class,
        _e.course_title
	FROM historic_career _c
    JOIN historic_student _s ON _c.historic_student = _s.matriculation
    JOIN full_exam _e ON _c.course = _e.course AND _c.identifier = _e.identifier AND _c.date = _e.date
);

--#endregion

--#region available_professor

CREATE OR REPLACE VIEW available_professor AS (
    SELECT _p.* FROM professor _p
    LEFT JOIN teaching _t ON _p.email = _t.professor
    GROUP BY _p.email
    HAVING COUNT(*) < 3
);

--#endregion
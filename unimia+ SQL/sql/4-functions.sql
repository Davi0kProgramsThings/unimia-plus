--#region get_teaching_correlations

CREATE OR REPLACE FUNCTION get_teaching_correlations(teaching.course%TYPE, teaching.identifier%TYPE) 
    RETURNS SETOF professor AS $$
DECLARE
	_professor professor;
BEGIN
    RETURN QUERY (
        SELECT _p.* FROM correlations _c
        JOIN professor _p ON _c.professor = _p.email
        WHERE _c.course = $1 AND _c.identifier = $2
    );
END;
$$ LANGUAGE 'plpgsql';

--#endregion

--#region get_teaching_prerequisites

CREATE OR REPLACE FUNCTION get_teaching_prerequisites(teaching.course%TYPE, teaching.identifier%TYPE) 
    RETURNS SETOF teaching AS $$
DECLARE
	_teaching teaching;
BEGIN
    RETURN QUERY (
        SELECT _t.* FROM prerequisites _p
        JOIN teaching _t ON _p.course = _t.course AND _p.requisite = _t.identifier
        WHERE _p.course = $1 AND _p.identifier = $2
    );
END;
$$ LANGUAGE 'plpgsql';

--#endregion

--#region get_courses

CREATE OR REPLACE FUNCTION get_courses(professor.email%TYPE) 
    RETURNS SETOF course AS $$
BEGIN
    RETURN QUERY (
        SELECT DISTINCT _c.* FROM course _c
        JOIN teaching _t ON _c.code = _t.course
        WHERE _t.professor = $1
    );
END;
$$ LANGUAGE 'plpgsql';

--#endregion

--#region get_full_teachings

CREATE OR REPLACE FUNCTION get_full_teachings(professor.email%TYPE) 
    RETURNS SETOF full_teaching AS $$
BEGIN
    RETURN QUERY (
        SELECT * FROM full_teaching
            WHERE professor = $1
    );
END;
$$ LANGUAGE 'plpgsql';

--#endregion

--#region get_full_exams

CREATE OR REPLACE FUNCTION get_full_exams(professor.email%TYPE) 
    RETURNS SETOF full_exam AS $$
BEGIN
    RETURN QUERY (
        SELECT * FROM full_exam
            WHERE teaching_professor = $1
    );
END;
$$ LANGUAGE 'plpgsql';

--#endregion

--#region get_full_career

CREATE OR REPLACE FUNCTION get_full_career(student.email%TYPE) 
    RETURNS SETOF full_career AS $$
BEGIN
    RETURN QUERY (
        SELECT * FROM full_career
            WHERE student = $1
    );
END;
$$ LANGUAGE 'plpgsql';

--#endregion

--#region get_full_historic_career

CREATE OR REPLACE FUNCTION get_full_historic_career(historic_student.matriculation%TYPE) 
    RETURNS SETOF full_historic_career AS $$
BEGIN
    RETURN QUERY (
        SELECT * FROM full_historic_career
            WHERE historic_student = $1
    );
END;
$$ LANGUAGE 'plpgsql';

--#endregion

--#region get_student_enrollments

CREATE OR REPLACE FUNCTION get_student_enrollments(student.email%TYPE)
    RETURNS SETOF full_exam AS $$
BEGIN
    RETURN QUERY (
        SELECT _e.* FROM full_exam _e
        JOIN career _c ON _e.course = _c.course AND _e.identifier = _c.identifier AND _e.date = _c.date
        WHERE _c.student = $1 AND _c.date > NOW()
    );
END;
$$ LANGUAGE 'plpgsql';

--#endregion

--#region get_exam_students

CREATE OR REPLACE FUNCTION get_exam_students(exam.course%TYPE, exam.identifier%TYPE, exam.date%TYPE)
    RETURNS SETOF full_career AS $$
BEGIN
    RETURN QUERY (
        SELECT * FROM full_career
            WHERE course = $1 AND
                  identifier = $2 AND 
                  date = $3
    );
END;
$$ LANGUAGE 'plpgsql';

--#endregion
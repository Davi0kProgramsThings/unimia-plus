--#region set_student_matriculation

CREATE OR REPLACE FUNCTION set_student_matriculation()
	RETURNS TRIGGER AS $$
DECLARE
	matriculation student.matriculation%TYPE;
BEGIN
	IF NEW.matriculation IS NULL THEN
		matriculation = (
			SELECT MAX(_q.matriculation) FROM (
				SELECT _s1.matriculation FROM student _s1
					UNION
						SELECT _s2.matriculation FROM historic_student _s2
			) AS _q
		);

		IF matriculation IS NULL THEN
			NEW.matriculation = '000000';
		ELSE
			NEW.matriculation = LPAD((matriculation::INTEGER + 1)::VARCHAR, 6, '0');
		END IF;

		RETURN NEW;
	END IF;
	
	PERFORM * FROM historic_student _s
		WHERE _s.matriculation = NEW.matriculation;
		
	IF FOUND THEN
		RAISE EXCEPTION 'Il numero di matricola % è già in uso da un altro record nella tabella <historic_student>.',
			NEW.matriculation;
	END IF;
		
	RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';

CREATE TRIGGER i_u_student
BEFORE INSERT OR UPDATE OF matriculation ON student
FOR EACH ROW EXECUTE PROCEDURE set_student_matriculation();

--#endregion

--#region check_course_years

CREATE OR REPLACE FUNCTION check_course_years()
	RETURNS TRIGGER AS $$
BEGIN
	PERFORM * FROM teaching _t
    	WHERE _t.course = NEW.code AND _t.year > NEW.years;

	IF FOUND THEN
		RAISE EXCEPTION 'Non è possibile aggiornare la colonna <years>: ci sono insegnamenti associati '
			'a questo corso di laurea con un valore della colonna <year> > %.', 
				NEW.years;
	END IF;

	RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';

CREATE TRIGGER u_course
BEFORE UPDATE OF years ON course
FOR EACH ROW EXECUTE PROCEDURE check_course_years();

--#endregion

--#region check_teaching_year

CREATE OR REPLACE FUNCTION check_teaching_year()
	RETURNS TRIGGER AS $$
DECLARE
	course_years course.years%TYPE;
BEGIN
	SELECT years INTO course_years FROM course
		WHERE code = NEW.course;
		
	IF NEW.year < 1 OR NEW.year > course_years THEN
		RAISE EXCEPTION 'Il valore della colonna <year> deve essere compreso tra 1 e %.',
			course_years;
	END IF;
	
	RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';

CREATE TRIGGER i_u_teaching
BEFORE INSERT OR UPDATE ON teaching
FOR EACH ROW EXECUTE PROCEDURE check_teaching_year();

--#endregion

--#region check_teaching_professor

CREATE OR REPLACE FUNCTION check_teaching_professor()
	RETURNS TRIGGER AS $$
DECLARE
	teachings INTEGER;
BEGIN
	teachings = (
		SELECT count(*) FROM teaching
			WHERE professor = NEW.professor
	);
		
	IF teachings = 3 THEN
		RAISE EXCEPTION 'Il professore % ha raggiunto il limite di insegnamenti massimo (3).',
			NEW.professor;
	END IF;
	
	RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';

CREATE TRIGGER i_u_teaching_01
BEFORE INSERT OR UPDATE OF professor ON teaching
FOR EACH ROW EXECUTE PROCEDURE check_teaching_professor();

--#endregion

--#region check_correlations_professor

CREATE OR REPLACE FUNCTION check_correlations_professor()
	RETURNS TRIGGER AS $$
DECLARE
	_professor professor.email%TYPE;
BEGIN
	SELECT professor INTO _professor FROM teaching
		WHERE course = NEW.course AND identifier = NEW.identifier;

	IF _professor = NEW.professor THEN
		RAISE EXCEPTION 'Il professore % è già associato a questo insegnamento.',
			NEW.professor;
	END IF;
	
	RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';

CREATE TRIGGER i_u_correlations
BEFORE INSERT OR UPDATE ON correlations
FOR EACH ROW EXECUTE PROCEDURE check_correlations_professor();

--#endregion

--#region check_prerequisites_requisite

CREATE OR REPLACE FUNCTION check_prerequisites_requisite()
	RETURNS TRIGGER AS $$
BEGIN
	IF NOT (
		SELECT (
			_t1.year = _t2.year AND 
			_t1.semester > _t2.semester OR 
			_t1.year > _t2.year
		) FROM teaching _t1,
			   teaching _t2
		  WHERE _t1.course = NEW.course AND
				_t1.identifier = NEW.identifier AND
				_t2.course = NEW.course AND
				_t2.identifier = NEW.requisite
	) THEN
		RAISE EXCEPTION 'I requisiti di un insegnamento devono precederlo nel programma '
			'del corso di laurea.';
	END IF;
	
	RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';

CREATE TRIGGER i_u_prerequisites
BEFORE INSERT OR UPDATE ON prerequisites
FOR EACH ROW EXECUTE PROCEDURE check_prerequisites_requisite();

--#endregion

--#region check_exam

CREATE OR REPLACE FUNCTION check_exam()
	RETURNS TRIGGER AS $$
DECLARE
	teaching_year teaching.year%TYPE;
BEGIN
	SELECT year INTO teaching_year FROM teaching
		WHERE course = NEW.course AND
			  identifier = NEW.identifier;

	PERFORM * FROM teaching _t
		JOIN exam _e ON _e.course = _t.course AND
					 	_e.identifier = _t.identifier AND
						_e.date = NEW.date
		WHERE _t.course = NEW.course AND
			  _t.identifier <> NEW.identifier AND
			  _t.year = teaching_year;
			  
	IF FOUND THEN
		RAISE EXCEPTION 'Non è possibile fissare più di un esame nella stessa data per insegnamenti '
			'differenti dello stesso corso di laurea.';
	END IF;
	
	RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';

CREATE TRIGGER i_u_exam
BEFORE INSERT OR UPDATE ON exam
FOR EACH ROW EXECUTE PROCEDURE check_exam();

--#endregion

--#region check_exam_date

CREATE OR REPLACE FUNCTION check_exam_date()
	RETURNS TRIGGER AS $$
BEGIN
	IF (CURRENT_DATE + 7) > NEW.date THEN
		RAISE EXCEPTION 'Non è possibile fissare un esame nei 7 giorni successivi alla data odierna.';
	END IF;
	
	RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';

CREATE TRIGGER i_u_exam_01
BEFORE INSERT OR UPDATE OF date ON exam
FOR EACH ROW EXECUTE PROCEDURE check_exam_date();

--#endregion

--#region check_career_course

CREATE OR REPLACE FUNCTION check_career_course()
	RETURNS TRIGGER AS $$
BEGIN
	PERFORM * FROM student
		WHERE email = NEW.student AND
		      course = NEW.course;
	
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Uno studente può iscriversi solo a esami di insegnamenti del suo corso di laurea.';
	END IF;
	
	RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';

CREATE TRIGGER i_u_career
BEFORE INSERT OR UPDATE ON career
FOR EACH ROW EXECUTE PROCEDURE check_career_course();

--#endregion
 
--#region check_career_mark

CREATE OR REPLACE FUNCTION check_career_mark()
	RETURNS TRIGGER AS $$
BEGIN
	PERFORM * FROM career
		WHERE student = NEW.student AND
			  course = NEW.course AND
			  identifier = NEW.identifier AND
			  date < NEW.date AND

			  mark_result >= 18 AND 
			  ( mark_status IS NULL OR
				mark_status = 'A' );
			  
	IF FOUND THEN
		RAISE EXCEPTION 'Lo studente % ha già una valutazione sufficiente per questo insegnamento.',
			NEW.student;
	END IF;
	
	RETURN NEW;			
END;
$$ LANGUAGE 'plpgsql';

CREATE TRIGGER i_u_career_01
BEFORE INSERT OR UPDATE ON career
FOR EACH ROW EXECUTE PROCEDURE check_career_mark();

--#endregion

--#region check_career_missing_requisites

CREATE OR REPLACE FUNCTION check_career_missing_requisites()
	RETURNS TRIGGER AS $$
BEGIN
	PERFORM * FROM prerequisites
		WHERE course = NEW.course AND
			  identifier = NEW.identifier AND
			  requisite NOT IN (
					SELECT identifier FROM career
						WHERE student = NEW.student AND
							  mark_result >= 18 AND
							  mark_status = 'A'
				);
		
	IF FOUND THEN
		RAISE EXCEPTION 'Lo studente % non rispetta i requisiti necessari per iscriversi a esami di questo insegnamento.',
			NEW.student;
	END IF;
	
	RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';

CREATE TRIGGER i_u_career_02
BEFORE INSERT OR UPDATE ON career
FOR EACH ROW EXECUTE PROCEDURE check_career_missing_requisites();

--#endregion

--#region check_career_student_already_enrolled

CREATE OR REPLACE FUNCTION check_career_student_already_enrolled()
	RETURNS TRIGGER AS $$
BEGIN
	PERFORM * FROM career
		WHERE student = NEW.student AND
			  course = NEW.course AND
			  identifier = NEW.identifier AND
			  date <> NEW.date AND

			  mark_publication IS NULL AND
			  mark_result IS NULL;
		
	IF FOUND THEN
		RAISE EXCEPTION 'Lo studente % è già iscritto a un esame di questo insegnamento.',
			NEW.student;
	END IF;
	
	RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';

CREATE TRIGGER i_u_career_03
BEFORE INSERT OR UPDATE ON career
FOR EACH ROW EXECUTE PROCEDURE check_career_student_already_enrolled();

--#endregion

--#region make_historic_student

CREATE OR REPLACE FUNCTION make_historic_student()
	RETURNS TRIGGER AS $$
BEGIN
	INSERT INTO historic_student(matriculation, name, surname,
								 course, telephone, address) 
		VALUES (OLD.matriculation, OLD.name, OLD.surname,
				OLD.course, OLD.telephone, OLD.address);
				
	RETURN NULL;
END;
$$ LANGUAGE 'plpgsql';

CREATE TRIGGER d_student
AFTER DELETE ON student
FOR EACH ROW EXECUTE PROCEDURE make_historic_student();

--#endregion

--#region populate_historic_career

CREATE OR REPLACE FUNCTION populate_historic_career()
	RETURNS TRIGGER AS $$
DECLARE
	_matriculation student.matriculation%TYPE;
BEGIN
	PERFORM * FROM student
		WHERE email = OLD.student;

	IF FOUND THEN
		RETURN NULL;
	END IF;

	IF OLD.mark_result < 18 OR
	   OLD.mark_result >= 18 AND
	   OLD.mark_status IS NOT NULL 
	THEN
		_matriculation = (
			SELECT matriculation FROM historic_student
				ORDER BY timestamp DESC
				LIMIT 1
		);
		   
        INSERT INTO historic_career
			VALUES (_matriculation, OLD.course, OLD.identifier,
					OLD.date, OLD.mark_publication, OLD.mark_result,
					OLD.mark_status);
	END IF;
				
	RETURN NULL;
END;
$$ LANGUAGE 'plpgsql';

CREATE TRIGGER d_career
AFTER DELETE ON career
FOR EACH ROW EXECUTE PROCEDURE populate_historic_career();

--#endregion

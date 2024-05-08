--
-- PostgreSQL database dump
--

-- Dumped from database version 15.3
-- Dumped by pg_dump version 15.3

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: email; Type: DOMAIN; Schema: public; Owner: postgres
--

CREATE DOMAIN public.email AS character varying(254)
	CONSTRAINT email_check CHECK (((VALUE)::text ~ '^[a-zA-Z0-9.!#$%&''*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$'::text));


ALTER DOMAIN public.email OWNER TO postgres;

--
-- Name: telephone; Type: DOMAIN; Schema: public; Owner: postgres
--

CREATE DOMAIN public.telephone AS character varying(10)
	CONSTRAINT telephone_check CHECK (((VALUE)::text ~ '^[0-9]{9,10}$'::text));


ALTER DOMAIN public.telephone OWNER TO postgres;

--
-- Name: website; Type: DOMAIN; Schema: public; Owner: postgres
--

CREATE DOMAIN public.website AS character varying(2048)
	CONSTRAINT website_check CHECK (((VALUE)::text ~ '^(http|ftp|https):\/\/([\w_-]+(?:(?:\.[\w_-]+)+))([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-])$'::text));


ALTER DOMAIN public.website OWNER TO postgres;

--
-- Name: check_career_course(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_career_course() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	PERFORM * FROM student
		WHERE email = NEW.student AND
		      course = NEW.course;
	
	IF NOT FOUND THEN
		RAISE EXCEPTION 'Uno studente può iscriversi solo a esami di insegnamenti del suo corso di laurea.';
	END IF;
	
	RETURN NEW;
END;
$$;


ALTER FUNCTION public.check_career_course() OWNER TO postgres;

--
-- Name: check_career_mark(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_career_mark() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.check_career_mark() OWNER TO postgres;

--
-- Name: check_career_missing_requisites(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_career_missing_requisites() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.check_career_missing_requisites() OWNER TO postgres;

--
-- Name: check_career_student_already_enrolled(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_career_student_already_enrolled() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.check_career_student_already_enrolled() OWNER TO postgres;

--
-- Name: check_correlations_professor(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_correlations_professor() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.check_correlations_professor() OWNER TO postgres;

--
-- Name: check_course_years(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_course_years() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.check_course_years() OWNER TO postgres;

--
-- Name: check_exam(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_exam() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.check_exam() OWNER TO postgres;

--
-- Name: check_exam_date(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_exam_date() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF (CURRENT_DATE + 7) > NEW.date THEN
		RAISE EXCEPTION 'Non è possibile fissare un esame nei 7 giorni successivi alla data odierna.';
	END IF;
	
	RETURN NEW;
END;
$$;


ALTER FUNCTION public.check_exam_date() OWNER TO postgres;

--
-- Name: check_prerequisites_requisite(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_prerequisites_requisite() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.check_prerequisites_requisite() OWNER TO postgres;

--
-- Name: check_teaching_professor(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_teaching_professor() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.check_teaching_professor() OWNER TO postgres;

--
-- Name: check_teaching_year(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_teaching_year() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.check_teaching_year() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: course; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.course (
    code character(3) NOT NULL,
    class character varying(5) NOT NULL,
    title character varying(100) NOT NULL,
    years integer NOT NULL,
    language text DEFAULT 'italiano'::text NOT NULL,
    CONSTRAINT course_language_check CHECK ((language = ANY (ARRAY['italiano'::text, 'inglese'::text, 'francesespagnolo'::text, 'tedesco'::text]))),
    CONSTRAINT course_years_check CHECK ((years = ANY (ARRAY[2, 3])))
);


ALTER TABLE public.course OWNER TO postgres;

--
-- Name: get_courses(public.email); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_courses(public.email) RETURNS SETOF public.course
    LANGUAGE plpgsql
    AS $_$
BEGIN
    RETURN QUERY (
        SELECT DISTINCT _c.* FROM course _c
        JOIN teaching _t ON _c.code = _t.course
        WHERE _t.professor = $1
    );
END;
$_$;


ALTER FUNCTION public.get_courses(public.email) OWNER TO postgres;

--
-- Name: career; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.career (
    student public.email NOT NULL,
    course character(3) NOT NULL,
    identifier character(3) NOT NULL,
    date date NOT NULL,
    mark_publication timestamp without time zone,
    mark_result integer,
    mark_status character(1),
    CONSTRAINT career_check CHECK (((mark_publication)::date >= date)),
    CONSTRAINT career_check1 CHECK ((((mark_publication IS NULL) AND (mark_result IS NULL) AND (mark_status IS NULL)) OR ((mark_publication IS NOT NULL) AND (mark_result < 18) AND (mark_status IS NULL)) OR ((mark_publication IS NOT NULL) AND (mark_result >= 18)))),
    CONSTRAINT career_mark_result_check CHECK (((mark_result >= 0) AND (mark_result <= 31))),
    CONSTRAINT career_mark_status_check CHECK ((mark_status = ANY (ARRAY['A'::bpchar, 'R'::bpchar])))
);


ALTER TABLE public.career OWNER TO postgres;

--
-- Name: exam; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.exam (
    course character(3) NOT NULL,
    identifier character(3) NOT NULL,
    date date NOT NULL,
    "time" text,
    place text
);


ALTER TABLE public.exam OWNER TO postgres;

--
-- Name: professor; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.professor (
    email public.email NOT NULL,
    password character varying(255) NOT NULL,
    name character varying(30) NOT NULL,
    surname character varying(30) NOT NULL,
    website public.website NOT NULL,
    workplace text NOT NULL,
    reception text NOT NULL,
    telephone public.telephone,
    address text
);


ALTER TABLE public.professor OWNER TO postgres;

--
-- Name: teaching; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.teaching (
    course character(3) NOT NULL,
    identifier character(3) NOT NULL,
    name character varying(50) NOT NULL,
    description text NOT NULL,
    year integer NOT NULL,
    semester integer NOT NULL,
    credits integer NOT NULL,
    professor public.email NOT NULL,
    CONSTRAINT teaching_credits_check CHECK ((credits = ANY (ARRAY[3, 6, 9, 12, 15]))),
    CONSTRAINT teaching_semester_check CHECK ((semester = ANY (ARRAY[1, 2])))
);


ALTER TABLE public.teaching OWNER TO postgres;

--
-- Name: full_teaching; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.full_teaching AS
 SELECT _t.course,
    _t.identifier,
    _t.name,
    _t.description,
    _t.year,
    _t.semester,
    _t.credits,
    _t.professor,
    _p.name AS professor_name,
    _p.surname AS professor_surname,
    _c.class AS course_class,
    _c.title AS course_title
   FROM ((public.teaching _t
     JOIN public.course _c ON ((_t.course = _c.code)))
     JOIN public.professor _p ON (((_t.professor)::text = (_p.email)::text)));


ALTER TABLE public.full_teaching OWNER TO postgres;

--
-- Name: full_exam; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.full_exam AS
 SELECT _e.course,
    _e.identifier,
    _e.date,
    _e."time",
    _e.place,
    _t.name AS teaching_name,
    _t.credits AS teaching_credits,
    _t.professor AS teaching_professor,
    _t.professor_name,
    _t.professor_surname,
    _t.course_class,
    _t.course_title
   FROM (public.exam _e
     JOIN public.full_teaching _t ON (((_e.course = _t.course) AND (_e.identifier = _t.identifier))));


ALTER TABLE public.full_exam OWNER TO postgres;

--
-- Name: student; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.student (
    email public.email NOT NULL,
    password character varying(255) NOT NULL,
    name character varying(30) NOT NULL,
    surname character varying(30) NOT NULL,
    matriculation character varying(6) NOT NULL,
    course character(3) NOT NULL,
    telephone public.telephone,
    address text
);


ALTER TABLE public.student OWNER TO postgres;

--
-- Name: full_career; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.full_career AS
 SELECT _c.student,
    _c.course,
    _c.identifier,
    _c.date,
    _c.mark_publication,
    _c.mark_result,
    _c.mark_status,
    _s.name AS student_name,
    _s.surname AS student_surname,
    _s.matriculation AS student_matriculation,
    _e.teaching_name,
    _e.teaching_credits,
    _e.teaching_professor,
    _e.professor_name,
    _e.professor_surname,
    _e.course_class,
    _e.course_title
   FROM ((public.career _c
     JOIN public.student _s ON (((_c.student)::text = (_s.email)::text)))
     JOIN public.full_exam _e ON (((_c.course = _e.course) AND (_c.identifier = _e.identifier) AND (_c.date = _e.date))));


ALTER TABLE public.full_career OWNER TO postgres;

--
-- Name: get_exam_students(character, character, date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_exam_students(character, character, date) RETURNS SETOF public.full_career
    LANGUAGE plpgsql
    AS $_$
BEGIN
    RETURN QUERY (
        SELECT * FROM full_career
            WHERE course = $1 AND
                  identifier = $2 AND 
                  date = $3
    );
END;
$_$;


ALTER FUNCTION public.get_exam_students(character, character, date) OWNER TO postgres;

--
-- Name: get_full_career(public.email); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_full_career(public.email) RETURNS SETOF public.full_career
    LANGUAGE plpgsql
    AS $_$
BEGIN
    RETURN QUERY (
        SELECT * FROM full_career
            WHERE student = $1
    );
END;
$_$;


ALTER FUNCTION public.get_full_career(public.email) OWNER TO postgres;

--
-- Name: get_full_exams(public.email); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_full_exams(public.email) RETURNS SETOF public.full_exam
    LANGUAGE plpgsql
    AS $_$
BEGIN
    RETURN QUERY (
        SELECT * FROM full_exam
            WHERE teaching_professor = $1
    );
END;
$_$;


ALTER FUNCTION public.get_full_exams(public.email) OWNER TO postgres;

--
-- Name: historic_career; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.historic_career (
    historic_student character varying(6) NOT NULL,
    course character(3) NOT NULL,
    identifier character(3) NOT NULL,
    date date NOT NULL,
    mark_publication timestamp without time zone NOT NULL,
    mark_result integer NOT NULL,
    mark_status character(1),
    CONSTRAINT historic_career_check CHECK (((mark_publication)::date >= date)),
    CONSTRAINT historic_career_mark_result_check CHECK (((mark_result >= 0) AND (mark_result <= 31))),
    CONSTRAINT historic_career_mark_status_check CHECK ((mark_status = ANY (ARRAY['A'::bpchar, 'R'::bpchar])))
);


ALTER TABLE public.historic_career OWNER TO postgres;

--
-- Name: historic_student; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.historic_student (
    matriculation character varying(6) NOT NULL,
    name character varying(30) NOT NULL,
    surname character varying(30) NOT NULL,
    "timestamp" timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    course character(3) NOT NULL,
    telephone public.telephone,
    address text
);


ALTER TABLE public.historic_student OWNER TO postgres;

--
-- Name: full_historic_career; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.full_historic_career AS
 SELECT _c.historic_student,
    _c.course,
    _c.identifier,
    _c.date,
    _c.mark_publication,
    _c.mark_result,
    _c.mark_status,
    _s.name AS student_name,
    _s.surname AS student_surname,
    _e.teaching_name,
    _e.teaching_credits,
    _e.teaching_professor,
    _e.professor_name,
    _e.professor_surname,
    _e.course_class,
    _e.course_title
   FROM ((public.historic_career _c
     JOIN public.historic_student _s ON (((_c.historic_student)::text = (_s.matriculation)::text)))
     JOIN public.full_exam _e ON (((_c.course = _e.course) AND (_c.identifier = _e.identifier) AND (_c.date = _e.date))));


ALTER TABLE public.full_historic_career OWNER TO postgres;

--
-- Name: get_full_historic_career(character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_full_historic_career(character varying) RETURNS SETOF public.full_historic_career
    LANGUAGE plpgsql
    AS $_$
BEGIN
    RETURN QUERY (
        SELECT * FROM full_historic_career
            WHERE historic_student = $1
    );
END;
$_$;


ALTER FUNCTION public.get_full_historic_career(character varying) OWNER TO postgres;

--
-- Name: get_full_teachings(public.email); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_full_teachings(public.email) RETURNS SETOF public.full_teaching
    LANGUAGE plpgsql
    AS $_$
BEGIN
    RETURN QUERY (
        SELECT * FROM full_teaching
            WHERE professor = $1
    );
END;
$_$;


ALTER FUNCTION public.get_full_teachings(public.email) OWNER TO postgres;

--
-- Name: get_student_enrollments(public.email); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_student_enrollments(public.email) RETURNS SETOF public.full_exam
    LANGUAGE plpgsql
    AS $_$
BEGIN
    RETURN QUERY (
        SELECT _e.* FROM full_exam _e
        JOIN career _c ON _e.course = _c.course AND _e.identifier = _c.identifier AND _e.date = _c.date
        WHERE _c.student = $1 AND _c.date > NOW()
    );
END;
$_$;


ALTER FUNCTION public.get_student_enrollments(public.email) OWNER TO postgres;

--
-- Name: get_teaching_correlations(character, character); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_teaching_correlations(character, character) RETURNS SETOF public.professor
    LANGUAGE plpgsql
    AS $_$
DECLARE
	_professor professor;
BEGIN
    RETURN QUERY (
        SELECT _p.* FROM correlations _c
        JOIN professor _p ON _c.professor = _p.email
        WHERE _c.course = $1 AND _c.identifier = $2
    );
END;
$_$;


ALTER FUNCTION public.get_teaching_correlations(character, character) OWNER TO postgres;

--
-- Name: get_teaching_prerequisites(character, character); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_teaching_prerequisites(character, character) RETURNS SETOF public.teaching
    LANGUAGE plpgsql
    AS $_$
DECLARE
	_teaching teaching;
BEGIN
    RETURN QUERY (
        SELECT _t.* FROM prerequisites _p
        JOIN teaching _t ON _p.course = _t.course AND _p.requisite = _t.identifier
        WHERE _p.course = $1 AND _p.identifier = $2
    );
END;
$_$;


ALTER FUNCTION public.get_teaching_prerequisites(character, character) OWNER TO postgres;

--
-- Name: insert_teaching(public.teaching, public.email[], character[]); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.insert_teaching(IN _teaching public.teaching, IN _correlations public.email[], IN _prerequisites character[])
    LANGUAGE plpgsql
    AS $$
DECLARE
	correlation EMAIL;

    prerequisite CHAR(3);
BEGIN
    INSERT INTO teaching VALUES (_teaching.course, _teaching.identifier, _teaching.name, _teaching.description, 
                                 _teaching.year, _teaching.semester, _teaching.credits, _teaching.professor); 

    FOREACH correlation IN ARRAY _correlations LOOP
        INSERT INTO correlations VALUES (correlation, _teaching.course, _teaching.identifier);
    END LOOP;

    FOREACH prerequisite IN ARRAY _prerequisites LOOP
        INSERT INTO prerequisites VALUES (_teaching.course, _teaching.identifier, prerequisite);
    END LOOP;
END;
$$;


ALTER PROCEDURE public.insert_teaching(IN _teaching public.teaching, IN _correlations public.email[], IN _prerequisites character[]) OWNER TO postgres;

--
-- Name: make_historic_student(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.make_historic_student() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	INSERT INTO historic_student(matriculation, name, surname,
								 course, telephone, address) 
		VALUES (OLD.matriculation, OLD.name, OLD.surname,
				OLD.course, OLD.telephone, OLD.address);
				
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.make_historic_student() OWNER TO postgres;

--
-- Name: populate_historic_career(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.populate_historic_career() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.populate_historic_career() OWNER TO postgres;

--
-- Name: set_student_matriculation(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.set_student_matriculation() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


ALTER FUNCTION public.set_student_matriculation() OWNER TO postgres;

--
-- Name: update_teaching(character, character, public.teaching, public.email[], character[]); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.update_teaching(IN _course character, IN _identifier character, IN _teaching public.teaching, IN _correlations public.email[], IN _prerequisites character[])
    LANGUAGE plpgsql
    AS $$
DECLARE
	correlation EMAIL;

    prerequisite CHAR(3);
BEGIN
    UPDATE teaching SET course=_teaching.course, identifier=_teaching.identifier, name=_teaching.name, 
                        description=_teaching.description, year=_teaching.year, semester=_teaching.semester, 
                        credits=_teaching.credits, professor=_teaching.professor
    WHERE course=_course AND identifier=_identifier;

    DELETE FROM correlations WHERE course=_teaching.course AND identifier=_teaching.identifier;

    FOREACH correlation IN ARRAY _correlations LOOP
        INSERT INTO correlations VALUES (correlation, _teaching.course, _teaching.identifier);
    END LOOP;

    DELETE FROM prerequisites WHERE course=_teaching.course AND identifier=_teaching.identifier;

    FOREACH prerequisite IN ARRAY _prerequisites LOOP
        INSERT INTO prerequisites VALUES (_teaching.course, _teaching.identifier, prerequisite);
    END LOOP;
END;
$$;


ALTER PROCEDURE public.update_teaching(IN _course character, IN _identifier character, IN _teaching public.teaching, IN _correlations public.email[], IN _prerequisites character[]) OWNER TO postgres;

--
-- Name: available_professor; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.available_professor AS
SELECT
    NULL::public.email AS email,
    NULL::character varying(255) AS password,
    NULL::character varying(30) AS name,
    NULL::character varying(30) AS surname,
    NULL::public.website AS website,
    NULL::text AS workplace,
    NULL::text AS reception,
    NULL::public.telephone AS telephone,
    NULL::text AS address;


ALTER TABLE public.available_professor OWNER TO postgres;

--
-- Name: correlations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.correlations (
    professor public.email NOT NULL,
    course character(3) NOT NULL,
    identifier character(3) NOT NULL
);


ALTER TABLE public.correlations OWNER TO postgres;

--
-- Name: full_historic_student; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.full_historic_student AS
 SELECT _s.matriculation,
    _s.name,
    _s.surname,
    _s."timestamp",
    _s.course,
    _s.telephone,
    _s.address,
    _c.class AS course_class,
    _c.title AS course_title
   FROM (public.historic_student _s
     JOIN public.course _c ON ((_s.course = _c.code)));


ALTER TABLE public.full_historic_student OWNER TO postgres;

--
-- Name: full_student; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.full_student AS
 SELECT _s.email,
    _s.password,
    _s.name,
    _s.surname,
    _s.matriculation,
    _s.course,
    _s.telephone,
    _s.address,
    _c.class AS course_class,
    _c.title AS course_title
   FROM (public.student _s
     JOIN public.course _c ON ((_s.course = _c.code)));


ALTER TABLE public.full_student OWNER TO postgres;

--
-- Name: prerequisites; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.prerequisites (
    course character(3) NOT NULL,
    identifier character(3) NOT NULL,
    requisite character(3) NOT NULL,
    CONSTRAINT prerequisites_check CHECK ((identifier <> requisite))
);


ALTER TABLE public.prerequisites OWNER TO postgres;

--
-- Name: secretary; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.secretary (
    email public.email NOT NULL,
    password character varying(255) NOT NULL,
    name character varying(30) NOT NULL,
    surname character varying(30) NOT NULL,
    telephone public.telephone,
    address text
);


ALTER TABLE public.secretary OWNER TO postgres;

--
-- Data for Name: career; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.career (student, course, identifier, date, mark_publication, mark_result, mark_status) FROM stdin;
giulio.verdi@studenti.unimi.it	F1X	0G-	2023-01-17	2023-01-24 16:34:00	16	\N
giulio.verdi@studenti.unimi.it	F1X	0J-	2023-01-16	2023-01-24 09:26:00	10	\N
giulio.verdi@studenti.unimi.it	F1X	0J-	2023-02-15	2023-02-22 13:12:00	15	\N
giulio.verdi@studenti.unimi.it	F1X	0G-	2023-02-20	2023-02-27 14:13:00	27	A
giulio.verdi@studenti.unimi.it	F1X	0J-	2023-06-21	2023-06-21 15:31:00	23	A
giulio.verdi@studenti.unimi.it	F1X	0F-	2023-01-23	2023-01-30 21:56:00	28	A
giulio.verdi@studenti.unimi.it	F1X	15-	2023-06-17	2023-06-24 15:03:00	21	R
giulio.verdi@studenti.unimi.it	F1X	15-	2023-07-19	2023-07-26 16:58:00	23	R
giulio.verdi@studenti.unimi.it	F1X	15-	2023-09-23	2023-09-30 13:11:00	30	A
giulio.verdi@studenti.unimi.it	F1X	12-	2023-09-15	2023-09-22 20:34:00	31	A
giulio.verdi@studenti.unimi.it	F1X	1S-	2023-07-28	2023-08-05 09:15:00	27	A
giulio.verdi@studenti.unimi.it	F1X	1K-	2023-06-15	2023-06-22 11:18:00	23	R
giulio.verdi@studenti.unimi.it	F1X	1K-	2023-07-21	2023-07-28 11:51:00	28	A
giulio.verdi@studenti.unimi.it	F1X	0C-	2024-01-13	2024-01-20 13:24:00	12	\N
giulio.verdi@studenti.unimi.it	F1X	0C-	2024-02-10	2024-02-10 14:07:00	25	A
giulio.verdi@studenti.unimi.it	F1X	2H-	2024-02-23	2024-03-02 19:46:00	20	R
giulio.verdi@studenti.unimi.it	F1X	1V-	2024-02-25	2024-03-04 10:31:00	8	\N
giulio.verdi@studenti.unimi.it	F1X	0B-	2024-05-16	\N	\N	\N
giulio.verdi@studenti.unimi.it	F1X	1V-	2024-05-13	\N	\N	\N
giulio.verdi@studenti.unimi.it	F1X	2H-	2024-05-11	\N	\N	\N
\.


--
-- Data for Name: correlations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.correlations (professor, course, identifier) FROM stdin;
fabio.cavalletti@docenti.unimi.it	F1X	0J-
anna.gori@docenti.unimi.it	F1X	0J-
nicola.basilico@docenti.unimi.it	F1X	12-
matteo.re@docenti.unimi.it	F1X	12-
massimo.rivolta@docenti.unimi.it	F1X	12-
camillo.fiorentini@docenti.unimi.it	F1X	15-
roberto.svaldi@docenti.unimi.it	F1X	1K-
violetta.lonati@docenti.unimi.it	F1X	0C-
nicola.basilico@docenti.unimi.it	F1X	0F-
matteo.re@docenti.unimi.it	F1X	0F-
massimo.rivolta@docenti.unimi.it	F1X	0F-
anna.morpurgo@docenti.unimi.it	F1X	0G-
andrea.trentini@docenti.unimi.it	F1X	0G-
valerio.bellandi@docenti.unimi.it	F1X	0B-
giovanni.livraga@docenti.unimi.it	F1X	0B-
ruggero.donida@docenti.unimi.it	F1X	1V-
mattia.monga@docenti.unimi.it	F1X	1U-
\.


--
-- Data for Name: course; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.course (code, class, title, years, language) FROM stdin;
F1X	L-31	Informatica	3	italiano
\.


--
-- Data for Name: exam; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.exam (course, identifier, date, "time", place) FROM stdin;
F1X	1U-	2024-09-11	14:30	Dipartimento di informatica
F1X	1U-	2025-01-20	14:30	Dipartimento di informatica
F1X	0F-	2023-01-23	8:30	Dipartimento di informatica
F1X	0F-	2023-02-09	8:30	Dipartimento di informatica
F1X	0F-	2023-06-14	8:30	Dipartimento di informatica
F1X	0F-	2023-07-08	8:30	Dipartimento di informatica
F1X	0F-	2023-09-17	8:30	Dipartimento di informatica
F1X	0J-	2023-01-16	11:00	Dipartimento di informatica
F1X	0J-	2023-02-15	11:00	Dipartimento di informatica
F1X	0J-	2023-06-21	11:00	Dipartimento di informatica
F1X	0J-	2023-07-12	11:00	Dipartimento di informatica
F1X	0J-	2023-09-24	11:00	Dipartimento di informatica
F1X	0G-	2023-01-17	9:30	Dipartimento di informatica
F1X	0G-	2023-02-20	9:30	Dipartimento di informatica
F1X	0G-	2023-06-13	9:30	Dipartimento di informatica
F1X	0G-	2023-07-15	9:30	Dipartimento di informatica
F1X	0G-	2023-09-07	9:30	Dipartimento di informatica
F1X	12-	2023-01-25	8:30	Dipartimento di informatica
F1X	12-	2023-02-28	8:30	Dipartimento di informatica
F1X	12-	2023-06-23	8:30	Dipartimento di informatica
F1X	12-	2023-07-24	8:30	Dipartimento di informatica
F1X	12-	2023-09-15	8:30	Dipartimento di informatica
F1X	1S-	2023-01-14	14:15	Dipartimento di informatica
F1X	1S-	2023-02-10	14:15	Dipartimento di informatica
F1X	1S-	2023-06-26	14:15	Dipartimento di informatica
F1X	1S-	2023-07-28	14:15	Dipartimento di informatica
F1X	1S-	2023-09-27	14:15	Dipartimento di informatica
F1X	15-	2023-01-22	15:00	Dipartimento di informatica
F1X	15-	2023-02-21	15:00	Dipartimento di informatica
F1X	15-	2023-06-17	15:00	Dipartimento di informatica
F1X	15-	2023-07-19	15:00	Dipartimento di informatica
F1X	15-	2023-09-23	15:00	Dipartimento di informatica
F1X	1K-	2023-01-11	10:30	Dipartimento di informatica
F1X	1K-	2023-02-13	10:30	Dipartimento di informatica
F1X	1K-	2023-06-15	10:30	Dipartimento di informatica
F1X	1K-	2023-07-21	10:30	Dipartimento di informatica
F1X	1K-	2023-09-26	10:30	Dipartimento di informatica
F1X	0C-	2024-01-13	9:00	Dipartimento di informatica
F1X	0C-	2024-02-10	9:00	Dipartimento di informatica
F1X	0C-	2024-06-15	9:00	Dipartimento di informatica
F1X	2H-	2024-01-21	9:30	Dipartimento di informatica
F1X	2H-	2024-02-23	9:30	Dipartimento di informatica
F1X	2H-	2024-05-11	9:30	Dipartimento di informatica
F1X	2H-	2024-06-13	9:30	Dipartimento di informatica
F1X	1V-	2024-01-19	14:30	Dipartimento di informatica
F1X	1V-	2024-02-25	14:30	Dipartimento di informatica
F1X	1V-	2024-06-28	14:30	Dipartimento di informatica
F1X	0B-	2024-06-20	10:00	Dipartimento di informatica
F1X	1T-	2024-05-14	8:00	Dipartimento di informatica
F1X	1T-	2024-06-17	8:00	Dipartimento di informatica
F1X	0C-	2024-05-17	9:00	Dipartimento di informatica
F1X	0B-	2024-05-16	10:00	Dipartimento di informatica
F1X	0A-	2024-06-21	9:30	Dipartimento di informatica
F1X	1U-	2024-06-17	14:30	Dipartimento di informatica
F1X	1V-	2024-07-23	14:30	Dipartimento di informatica
F1X	1V-	2024-09-16	14:30	Dipartimento di informatica
F1X	0C-	2024-07-15	9:00	Dipartimento di informatica
F1X	0C-	2024-09-21	9:00	Dipartimento di informatica
F1X	2H-	2024-07-05	9:30	Dipartimento di informatica
F1X	2H-	2024-09-10	9:30	Dipartimento di informatica
F1X	1T-	2024-07-21	8:00	Dipartimento di informatica
F1X	1T-	2024-09-23	8:00	Dipartimento di informatica
F1X	0B-	2024-07-17	10:00	Dipartimento di informatica
F1X	0B-	2024-09-28	10:00	Dipartimento di informatica
F1X	0A-	2024-07-14	9:30	Dipartimento di informatica
F1X	0A-	2024-09-18	9:30	Dipartimento di informatica
F1X	0A-	2025-01-23	9:30	Dipartimento di informatica
F1X	1U-	2024-07-09	14:30	Dipartimento di informatica
F1X	1V-	2024-05-13	14:30	Dipartimento di informatica
\.


--
-- Data for Name: historic_career; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.historic_career (historic_student, course, identifier, date, mark_publication, mark_result, mark_status) FROM stdin;
\.


--
-- Data for Name: historic_student; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.historic_student (matriculation, name, surname, "timestamp", course, telephone, address) FROM stdin;
\.


--
-- Data for Name: prerequisites; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.prerequisites (course, identifier, requisite) FROM stdin;
F1X	12-	0F-
F1X	0C-	0J-
F1X	0C-	1K-
F1X	0C-	0G-
F1X	2H-	0G-
F1X	1T-	0J-
F1X	0B-	0G-
F1X	1V-	0G-
F1X	0A-	0F-
F1X	0A-	12-
F1X	0A-	1V-
F1X	1U-	0G-
F1X	1U-	2H-
\.


--
-- Data for Name: professor; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.professor (email, password, name, surname, website, workplace, reception, telephone, address) FROM stdin;
nicola.basilico@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Nicola	Basilico	http://basilico.di.unimi.it/	Via Celoria, 18	Su appuntamento per email	0250316289	\N
paolo.boldi@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Paolo	Boldi	https://boldi.di.unimi.it/	Via Celoria, 18	Via Celoria, 18 (V piano)	0250316305	\N
roberto.svaldi@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Roberto	Svaldi	https://rsvaldi.github.io/	Via Saldini, 50	Studio 2102, Dipartimento di Matematica "F. Enriques", Via Saldini 50	0250316153	\N
matteo.re@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Matteo	Re	https://homes.di.unimi.it/re/	Via Celoria, 18	Milano - via Celoria 18 (stanza 3010)	0250316209	\N
fabio.cavalletti@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Fabio	Cavalletti	https://sites.google.com/view/cavallet	Via Saldini, 50	Su appuntamento	0250316157	\N
anna.gori@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Anna	Gori	http://www.mat.unimi.it/users/gori/Sito/Home.html	Via Saldini, 50	stanza 2045 Dipartimento di matematica	\N	\N
alberto.borghese@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Alberto N.	Borghese	http://homes.di.unimi.it/borghese/	Via Celoria, 18	Dipartimento di Informatica	0250316325	\N
claudia.bucur@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Claudia D.	Bucur	https://sites.google.com/view/claudiabucurmaths/home	Via Saldini, 50	Ufficio 1025, Dipartimento di Matematica	0250316163	\N
anna.morpurgo@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Anna C. G.	Morpurgo	http://homes.di.unimi.it/~morpurgo/home.html	Via Celoria, 18	ufficio 5003 via Celoria 18	0250316316	\N
beatrice.palano@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Beatrice S.	Palano	http://palano.di.unimi.it/	Via Celoria, 18	Via Celoria, 18 - stanza: 4011	0250316254	\N
stefano.aguzzoli@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Stefano	Aguzzoli	http://homes.di.unimi.it/~aguzzoli	Via Celoria, 18	stanza 4010 via Celoria 18	0250316356	\N
camillo.fiorentini@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Camillo	Fiorentini	https://fiorentini.di.unimi.it/	Via Celoria, 18	vedi https://fiorentini.di.unimi.it/	0250316269	\N
chiara.camere@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Chiara	Camere	http://www.mat.unimi.it/users/camere/it/index.html	Via Saldini, 50	Dipartimento di Matematica - Ufficio 2070	\N	\N
violetta.lonati@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Violetta	Lonati	http://lonati.di.unimi.it/	Via Celoria, 18	Dipartimento di Informatica oppure online	0250316292	\N
giovanni.pighizzini@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Giovanni	Pighizzini	http://pighizzini.di.unimi.it/	Via Celoria, 18	Il ricevimento studenti viene svolto sia in presenza (modalità preferibile), sia a distanza.	0250316000	\N
massimo.santini@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Massimo	Santini	https://santini.di.unimi.it/	Via Celoria, 18	stanza 5007, V piano, via Celoria, 18	0250316259	\N
ruggero.donida@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Ruggero	Donida	http://homes.di.unimi.it/donida/	Via Celoria, 18	Su appuntamento via email	0250316377	\N
vincenzo.piuri@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Vincenzo	Piuri	https://piuri.di.unimi.it/	Via Celoria, 18	Dipartimento di informatica	0250316244	\N
massimo.rivolta@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Massimo W.	Rivolta	https://homes.di.unimi.it/rivolta	Via Celoria, 18	Stanza 6021	0250316347	\N
andrea.trentini@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Andrea M.	Trentini	http://atrent.it/	Via Celoria, 18	stanza 4007, via Celoria 18, MI	0250316274	\N
valerio.bellandi@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Valerio	Bellandi	https://expertise.unimi.it/get/person/valerio-bellandi	Via Celoria, 18	stanza 7008	0250316221	\N
giovanni.livraga@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Giovanni	Livraga	http://homes.di.unimi.it/livraga/	Via Celoria, 18	su appuntamento tramite mail	0250316339	\N
stefano.montanelli@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Stefano	Montanelli	http://islab.di.unimi.it/montanelli/	Via Celoria, 18	Stanza 7015, Dipartimento di Informatica "Giovanni degli Antoni", Via Celoria 18 - 20133 Milano	0250316283	\N
dario.malchiodi@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Dario	Malchiodi	https://malchiodi.di.unimi.it/	Via Celoria, 18	Stanza 5015 del Dipartimento di Informatica	0250316338	\N
mattia.monga@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Mattia	Monga	https://homes.di.unimi.it/monga/index.html	Via Celoria, 18	Uff. 5004, Via Celoria 18, Milano	0250316238	\N
carlo.bellettini@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Carlo N. M.	Bellettini	https://bellettini.di.unimi.it/	Via Celoria, 18	ufficio 5006 Via Celoria 18 - Milano	0250316255	\N
elena.pagani@docenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Elena	Pagani	https://homes.di.unimi.it/~pagae/	Via Celoria, 18	online (teams) o ufficio	0250316271	\N
\.


--
-- Data for Name: secretary; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.secretary (email, password, name, surname, telephone, address) FROM stdin;
mario.rossi@segreteria.unimi.it	25d55ad283aa400af464c76d713c07ad	Mario	Rossi	\N	\N
\.


--
-- Data for Name: student; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.student (email, password, name, surname, matriculation, course, telephone, address) FROM stdin;
giulio.verdi@studenti.unimi.it	25d55ad283aa400af464c76d713c07ad	Giulio	Verdi	000000	F1X	5015146461	337 Ipwo Road
\.


--
-- Data for Name: teaching; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.teaching (course, identifier, name, description, year, semester, credits, professor) FROM stdin;
F1X	0J-	Matematica del continuo	L'obiettivo dell'insegnamento è duplice. Anzitutto, fornire agli studenti un linguaggio matematico di base, che li metta grado di formulare correttamente un problema e di comprendere un problema formulato da altri. Inoltre, fornire gli strumenti matematici indispensabili per la soluzione di alcuni problemi specifici, che spaziano dal comportamento delle successioni a quello delle serie e delle funzioni di una variabile.	1	1	12	claudia.bucur@docenti.unimi.it
F1X	0G-	Programmazione	Obiettivo dell'insegnamento e' introdurre gli studenti alla programmazione imperativa strutturata e al problem solving in piccolo	1	1	12	paolo.boldi@docenti.unimi.it
F1X	1T-	Statistica e analisi dei dati	L'insegnamento ha lo scopo di introdurre i concetti fondamentali della statistica descrittiva, del calcolo delle probabilità e della statistica inferenziale parametrica.	2	2	6	dario.malchiodi@docenti.unimi.it
F1X	12-	Architettura degli elaboratori II	L'insegnamento fornisce la conoscenza del funzionamento delle architetture digitali approfondendo in particolare la pipe-line, i multi-core e le gerarchie di memoria in modo da potere capire a fondo le problematiche legate ai sistemi operativi e all'ottimizzazione del software. Vengono forniti gli strumenti per valutare le prestazioni dei calcolatori e per ottimizzare le applicazioni.	1	2	6	alberto.borghese@docenti.unimi.it
F1X	1S-	Linguaggi formali e automi	L'insegnamento si prefigge il compito di presentare i concetti della teoria dei linguaggi formali e degli automi centrali in svariati ambiti del contesto informatico attuale, abituando lo studente all'uso di metodi formali.	1	2	6	beatrice.palano@docenti.unimi.it
F1X	15-	Logica matematica	L'insegnamento ha lo scopo di introdurre i principi fondamentali del ragionamento razionale, tramite l'approccio formale fornito dalla logica matemaica, sia a livello proposizionale che a livello predicativo.	1	2	6	stefano.aguzzoli@docenti.unimi.it
F1X	1K-	Matematica del discreto	Gli obiettivi principali dell'insegnamento sono di introdurre il linguaggio dell'algebra e le nozioni di spazio vettoriale e applicazioni lineari e di analizzare il problema della risolubilità dei sistemi di equazioni lineari (anche da un punto di vista algoritmico)	1	2	6	chiara.camere@docenti.unimi.it
F1X	0C-	Algoritmi e strutture dati	L'insegnamento ha lo scopo di introdurre i concetti fondamentali riguardanti il progetto e l'analisi di algoritmi e delle strutture dati che essi utilizzano, illustrando le principali tecniche di progettazione e alcune strutture dati fondamentali, insieme all'analisi della complessità computazionale.	2	1	12	giovanni.pighizzini@docenti.unimi.it
F1X	2H-	Programmazione II	L'insegnamento, che si colloca nel percorso ideale iniziato dall'insegnamento di "Programmazione" e che proseguirà nell'insegnamento di "Ingegneria del software", ha l'obiettivo di presentare alcune astrazioni e concetti utili al progetto, sviluppo e manutenzione di programmi di grandi dimensioni. L'attenzione è focalizzata sul paradigma orientato agli oggetti, con particolare enfasi riguardo al processo di specificazione, modellazione dei tipi di dato e progetto.	2	1	6	massimo.santini@docenti.unimi.it
F1X	0F-	Architettura degli elaboratori I	L'insegnamento introduce le conoscenze dei principi che sottendono al funzionamento di un elaboratore digitale; partendo dal livello delle porte logiche si arriva, attraverso alcuni livelli di astrazione intermedi, alla progettazione di ALU firmware e di un'architettura MIPS in grado di eseguire il nucleo delle istruzioni in linguaggio macchina.	1	1	6	alberto.borghese@docenti.unimi.it
F1X	0B-	Basi di dati	L'insegnamento fornisce i concetti fondamentali relativi alle basi di dati e ai sistemi per la loro gestione, con particolare riguardo ai sistemi di basi di dati relazionali. Il corso prevede i) una parte di teoria dedicata a modelli, linguaggi, metodologie di progettazione e agli aspetti di sicurezza e transazioni, e ii) una parte di laboratorio dedicata all'uso di strumenti di progettazione e gestione di basi di dati relazionali e alle principali tecnologie di basi di dati e Web.	2	2	12	stefano.montanelli@docenti.unimi.it
F1X	1V-	Sistemi operativi	L'insegnamento si propone di fornire le conoscenze sui fondamenti teorici, gli algoritmi e le tecnologie riguardanti l'architettura complessiva e la gestione del processore, della memoria centrale, dei dispositivi di ingresso/uscita, del file system, dell'interfaccia utente e degli ambienti distribuiti nei sistemi operativi per le principali tipologie di architetture di elaborazione.	2	1	12	vincenzo.piuri@docenti.unimi.it
F1X	0A-	Reti di calcolatori	L'insegnamento di reti di calcolatori è il corso di introduzione al networking, ed ha come principale obiettivo quello di fornire i principi dei protocolli e della architettura della rete internet occupandosi di servizi e protocolli ad ogni livello dell'architettura funzionale	3	1	12	elena.pagani@docenti.unimi.it
F1X	1U-	Insegneria del software	L'obiettivo dell'insegnamento è fornire agli studenti la conoscenza dei modelli e degli strumenti per l'analisi, il progetto, lo sviluppo e il collaudo dei sistemi software, e di metterli in grado di progettare, sviluppare e collaudare sistemi software.	3	1	12	carlo.bellettini@docenti.unimi.it
\.


--
-- Name: career career_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.career
    ADD CONSTRAINT career_pkey PRIMARY KEY (student, course, identifier, date);


--
-- Name: correlations correlations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.correlations
    ADD CONSTRAINT correlations_pkey PRIMARY KEY (professor, course, identifier);


--
-- Name: course course_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.course
    ADD CONSTRAINT course_pkey PRIMARY KEY (code);


--
-- Name: exam exam_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.exam
    ADD CONSTRAINT exam_pkey PRIMARY KEY (course, identifier, date);


--
-- Name: historic_career historic_career_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historic_career
    ADD CONSTRAINT historic_career_pkey PRIMARY KEY (historic_student, course, identifier, date);


--
-- Name: historic_student historic_student_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historic_student
    ADD CONSTRAINT historic_student_pkey PRIMARY KEY (matriculation);


--
-- Name: prerequisites prerequisites_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.prerequisites
    ADD CONSTRAINT prerequisites_pkey PRIMARY KEY (course, identifier, requisite);


--
-- Name: professor professor_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.professor
    ADD CONSTRAINT professor_pkey PRIMARY KEY (email);


--
-- Name: secretary secretary_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.secretary
    ADD CONSTRAINT secretary_pkey PRIMARY KEY (email);


--
-- Name: student student_matriculation_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.student
    ADD CONSTRAINT student_matriculation_key UNIQUE (matriculation);


--
-- Name: student student_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.student
    ADD CONSTRAINT student_pkey PRIMARY KEY (email);


--
-- Name: teaching teaching_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.teaching
    ADD CONSTRAINT teaching_pkey PRIMARY KEY (course, identifier);


--
-- Name: available_professor _RETURN; Type: RULE; Schema: public; Owner: postgres
--

CREATE OR REPLACE VIEW public.available_professor AS
 SELECT _p.email,
    _p.password,
    _p.name,
    _p.surname,
    _p.website,
    _p.workplace,
    _p.reception,
    _p.telephone,
    _p.address
   FROM (public.professor _p
     LEFT JOIN public.teaching _t ON (((_p.email)::text = (_t.professor)::text)))
  GROUP BY _p.email
 HAVING (count(*) < 3);


--
-- Name: career d_career; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER d_career AFTER DELETE ON public.career FOR EACH ROW EXECUTE FUNCTION public.populate_historic_career();


--
-- Name: student d_student; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER d_student AFTER DELETE ON public.student FOR EACH ROW EXECUTE FUNCTION public.make_historic_student();


--
-- Name: career i_u_career; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER i_u_career BEFORE INSERT OR UPDATE ON public.career FOR EACH ROW EXECUTE FUNCTION public.check_career_course();


--
-- Name: career i_u_career_01; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER i_u_career_01 BEFORE INSERT OR UPDATE ON public.career FOR EACH ROW EXECUTE FUNCTION public.check_career_mark();


--
-- Name: career i_u_career_02; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER i_u_career_02 BEFORE INSERT OR UPDATE ON public.career FOR EACH ROW EXECUTE FUNCTION public.check_career_missing_requisites();


--
-- Name: career i_u_career_03; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER i_u_career_03 BEFORE INSERT OR UPDATE ON public.career FOR EACH ROW EXECUTE FUNCTION public.check_career_student_already_enrolled();


--
-- Name: correlations i_u_correlations; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER i_u_correlations BEFORE INSERT OR UPDATE ON public.correlations FOR EACH ROW EXECUTE FUNCTION public.check_correlations_professor();


--
-- Name: exam i_u_exam; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER i_u_exam BEFORE INSERT OR UPDATE ON public.exam FOR EACH ROW EXECUTE FUNCTION public.check_exam();


--
-- Name: exam i_u_exam_01; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER i_u_exam_01 BEFORE INSERT OR UPDATE OF date ON public.exam FOR EACH ROW EXECUTE FUNCTION public.check_exam_date();


--
-- Name: prerequisites i_u_prerequisites; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER i_u_prerequisites BEFORE INSERT OR UPDATE ON public.prerequisites FOR EACH ROW EXECUTE FUNCTION public.check_prerequisites_requisite();


--
-- Name: student i_u_student; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER i_u_student BEFORE INSERT OR UPDATE OF matriculation ON public.student FOR EACH ROW EXECUTE FUNCTION public.set_student_matriculation();


--
-- Name: teaching i_u_teaching; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER i_u_teaching BEFORE INSERT OR UPDATE ON public.teaching FOR EACH ROW EXECUTE FUNCTION public.check_teaching_year();


--
-- Name: teaching i_u_teaching_01; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER i_u_teaching_01 BEFORE INSERT OR UPDATE OF professor ON public.teaching FOR EACH ROW EXECUTE FUNCTION public.check_teaching_professor();


--
-- Name: course u_course; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER u_course BEFORE UPDATE OF years ON public.course FOR EACH ROW EXECUTE FUNCTION public.check_course_years();


--
-- Name: career career_course_identifier_date_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.career
    ADD CONSTRAINT career_course_identifier_date_fkey FOREIGN KEY (course, identifier, date) REFERENCES public.exam(course, identifier, date) ON UPDATE CASCADE;


--
-- Name: career career_student_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.career
    ADD CONSTRAINT career_student_fkey FOREIGN KEY (student) REFERENCES public.student(email) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: correlations correlations_course_identifier_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.correlations
    ADD CONSTRAINT correlations_course_identifier_fkey FOREIGN KEY (course, identifier) REFERENCES public.teaching(course, identifier) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: correlations correlations_professor_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.correlations
    ADD CONSTRAINT correlations_professor_fkey FOREIGN KEY (professor) REFERENCES public.professor(email) ON UPDATE CASCADE;


--
-- Name: exam exam_course_identifier_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.exam
    ADD CONSTRAINT exam_course_identifier_fkey FOREIGN KEY (course, identifier) REFERENCES public.teaching(course, identifier) ON UPDATE CASCADE;


--
-- Name: historic_career historic_career_course_identifier_date_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historic_career
    ADD CONSTRAINT historic_career_course_identifier_date_fkey FOREIGN KEY (course, identifier, date) REFERENCES public.exam(course, identifier, date) ON UPDATE CASCADE;


--
-- Name: historic_career historic_career_historic_student_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historic_career
    ADD CONSTRAINT historic_career_historic_student_fkey FOREIGN KEY (historic_student) REFERENCES public.historic_student(matriculation) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: historic_student historic_student_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historic_student
    ADD CONSTRAINT historic_student_course_fkey FOREIGN KEY (course) REFERENCES public.course(code) ON UPDATE CASCADE;


--
-- Name: prerequisites prerequisites_course_identifier_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.prerequisites
    ADD CONSTRAINT prerequisites_course_identifier_fkey FOREIGN KEY (course, identifier) REFERENCES public.teaching(course, identifier) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: prerequisites prerequisites_course_requisite_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.prerequisites
    ADD CONSTRAINT prerequisites_course_requisite_fkey FOREIGN KEY (course, requisite) REFERENCES public.teaching(course, identifier) ON UPDATE CASCADE;


--
-- Name: student student_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.student
    ADD CONSTRAINT student_course_fkey FOREIGN KEY (course) REFERENCES public.course(code) ON UPDATE CASCADE;


--
-- Name: teaching teaching_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.teaching
    ADD CONSTRAINT teaching_course_fkey FOREIGN KEY (course) REFERENCES public.course(code) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: teaching teaching_professor_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.teaching
    ADD CONSTRAINT teaching_professor_fkey FOREIGN KEY (professor) REFERENCES public.professor(email) ON UPDATE CASCADE;


--
-- PostgreSQL database dump complete
--


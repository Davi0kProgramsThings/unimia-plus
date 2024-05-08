--#region insert_teaching

CREATE OR REPLACE PROCEDURE insert_teaching(
    _teaching teaching,
    _correlations EMAIL[],
    _prerequisites CHAR(3)[]
) AS $$
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
$$ LANGUAGE 'plpgsql';

--#endregion

--#region update_teaching

CREATE OR REPLACE PROCEDURE update_teaching(
    _course teaching.course%TYPE,
    _identifier teaching.identifier%TYPE,
    _teaching teaching,
    _correlations EMAIL[],
    _prerequisites CHAR(3)[]
) AS $$
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
$$ LANGUAGE 'plpgsql';

--#endregion
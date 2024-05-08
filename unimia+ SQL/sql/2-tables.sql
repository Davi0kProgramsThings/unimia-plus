--#region secretary

CREATE TABLE secretary(
	email EMAIL PRIMARY KEY,
	password VARCHAR(255) NOT NULL,
	name VARCHAR(30) NOT NULL,
	surname VARCHAR(30) NOT NULL,
	telephone TELEPHONE,
	address TEXT
);

--#endregion

--#region professor

CREATE TABLE professor(
	email EMAIL PRIMARY KEY,
	password VARCHAR(255) NOT NULL,
	name VARCHAR(30) NOT NULL,
	surname VARCHAR(30) NOT NULL,
	website WEBSITE NOT NULL,
	workplace TEXT NOT NULL,
	reception TEXT NOT NULL,
	telephone TELEPHONE,
	address TEXT
);

--#endregion

--#region student

CREATE TABLE student(
	email EMAIL PRIMARY KEY,
	password VARCHAR(255) NOT NULL,
	name VARCHAR(30) NOT NULL,
	surname VARCHAR(30) NOT NULL,
	matriculation VARCHAR(6) NOT NULL UNIQUE,
	course CHAR(3) NOT NULL 
		REFERENCES course(code)
		ON UPDATE CASCADE ON DELETE NO ACTION,
	telephone TELEPHONE,
	address TEXT
);

--#endregion

--#region course

CREATE TABLE course(
	code CHAR(3) PRIMARY KEY,
	class VARCHAR(5) NOT NULL,
	title VARCHAR(100) NOT NULL,
	years INTEGER NOT NULL 
		CHECK (years IN (2, 3)),
	language TEXT NOT NULL 
		DEFAULT 'italiano'
		CHECK (language IN (
			'italiano', 'inglese', 'francese'
			'spagnolo', 'tedesco'))
);

--#endregion

--#region teaching

CREATE TABLE teaching(
	course CHAR(3)
		REFERENCES course(code)
		ON UPDATE CASCADE ON DELETE CASCADE,
	identifier CHAR(3),
	name VARCHAR(50) NOT NULL,
	description TEXT NOT NULL,
	year INTEGER NOT NULL,
	semester INTEGER NOT NULL
		CHECK (semester IN (1, 2)),
	credits INTEGER NOT NULL
		CHECK (credits IN (3, 6, 9, 12, 15)),
	professor EMAIL NOT NULL 
		REFERENCES professor(email)
		ON UPDATE CASCADE ON DELETE NO ACTION,
	
	PRIMARY KEY (course, identifier)
);

--#endregion

--#region exam

CREATE TABLE exam(
	course CHAR(3),
	identifier CHAR(3),
	date DATE,
	time TEXT,
	place TEXT,

	PRIMARY KEY(course, identifier, date),

	FOREIGN KEY(course, identifier)
		REFERENCES teaching(course, identifier)
		ON UPDATE CASCADE ON DELETE NO ACTION
);

--#endregion

--#region correlations

CREATE TABLE correlations(
	professor EMAIL
		REFERENCES professor(email)
		ON UPDATE CASCADE ON DELETE NO ACTION,
	course CHAR(3),
	identifier CHAR(3),
	
	PRIMARY KEY(professor, course, identifier),
	
	FOREIGN KEY(course, identifier)
		REFERENCES teaching(course, identifier)
		ON UPDATE CASCADE ON DELETE CASCADE
);

--#endregion

--#region prerequisites

CREATE TABLE prerequisites(
	course CHAR(3),
	identifier CHAR(3),
	requisite CHAR(3),
	
	PRIMARY KEY (course, identifier, requisite),
	
	FOREIGN KEY (course, identifier)
		REFERENCES teaching(course, identifier)
		ON UPDATE CASCADE ON DELETE CASCADE,
	
	FOREIGN KEY (course, requisite)
		REFERENCES teaching(course, identifier)
		ON UPDATE CASCADE ON DELETE NO ACTION,
	
	CHECK (identifier != requisite)
);

--#endregion

--#region career

CREATE TABLE career(
	student EMAIL
		REFERENCES student(email)
		ON UPDATE CASCADE ON DELETE CASCADE,
	course CHAR(3),
	identifier CHAR(3),
	date DATE,

	mark_publication TIMESTAMP
		CHECK (mark_publication::DATE >= date),
	mark_result INTEGER
		CHECK (mark_result >= 0 AND mark_result <= 31),
	mark_status CHAR(1)
		CHECK (mark_status IN ('A', 'R')),
	
	PRIMARY KEY(student, course, identifier, date),
	
	FOREIGN KEY(course, identifier, date)
		REFERENCES exam(course, identifier, date)
		ON UPDATE CASCADE ON DELETE NO ACTION,
	
	CHECK (
		(mark_publication IS NULL AND mark_result IS NULL AND mark_status IS NULL) OR 
		(mark_publication IS NOT NULL AND mark_result < 18 AND mark_status IS NULL) OR
		(mark_publication IS NOT NULL AND mark_result >= 18)
	)
);

--#endregion

--#region historic_student

CREATE TABLE historic_student(
	matriculation VARCHAR(6) PRIMARY KEY,
	name VARCHAR(30) NOT NULL,
	surname VARCHAR(30) NOT NULL,
	timestamp TIMESTAMP NOT NULL 
		DEFAULT CURRENT_TIMESTAMP,
	course CHAR(3) NOT NULL 
		REFERENCES course(code)
		ON UPDATE CASCADE ON DELETE NO ACTION,
	telephone TELEPHONE,
	address TEXT
);

--#endregion

--#region historic_career

CREATE TABLE historic_career(
	historic_student VARCHAR(6)
		REFERENCES historic_student(matriculation)
		ON UPDATE CASCADE ON DELETE CASCADE,
	course CHAR(3),
	identifier CHAR(3),
	date DATE,

	mark_publication TIMESTAMP NOT NULL
		CHECK (mark_publication::DATE >= date),
	mark_result INTEGER NOT NULL
		CHECK (mark_result >= 0 AND mark_result <= 31),
	mark_status CHAR(1)
		CHECK (mark_status IN ('A', 'R')),
	
	PRIMARY KEY(historic_student, course, identifier, date),
	
	FOREIGN KEY(course, identifier, date)
		REFERENCES exam(course, identifier, date)
		ON UPDATE CASCADE ON DELETE NO ACTION
);

--#endregion

SECRETARY(<u>email</u>, password, name, surname, telephone*, address*); \
PROFESSOR(<u>email</u>, password, name, surname, website, workplace, reception, telephone*, address*); \
STUDENT(<u>email</u>, password, name, surname, matriculation, _course_, telephone*, address*); \
COURSE(<u>code</u>, class, title, years, language); \
TEACHING(<u>_course_</u>, <u>identifier</u>, name, description, year, semester, credits, _professor_); \
EXAM(<u>_course_</u>, <u>_identifier_</u>, <u>date</u>, time, place);

CORRELATIONS(<u>_professor_</u>, <u>_course_</u>, <u>_identifier_</u>); \
PREREQUISITES(<u>_course_</u>, <u>_identifier_</u>, <u>_requisite_</u>); \
CAREER(<u>_student_</u>, <u>_course_</u>, <u>_identifier_</u>, <u>_date_</u>, mark_publication*, mark_result*, mark_status*);

HISTORIC_STUDENT(<u>matriculation</u>, name, surname, timestamp, _course_, telephone*, address*);

HISTORIC_CAREER(<u>_historic_student_</u>, <u>_course_</u>, <u>_identifier_</u>, <u>_date_</u>, mark_publication, mark_result, mark_status*);

\* attributes are optional.
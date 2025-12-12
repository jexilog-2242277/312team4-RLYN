--
-- PostgreSQL database dump
--

\restrict GSKlkaNOJa1TAHZsygS7RcyhGE6T5meBnUwhkWux5QStWBj4QI17GaDra9bhudP

-- Dumped from database version 18.0
-- Dumped by pg_dump version 18.0

-- Started on 2025-12-12 00:55:14

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 5 (class 2615 OID 2200)
-- Name: public; Type: SCHEMA; Schema: -; Owner: postgres
--

-- *not* creating schema, since initdb creates it


ALTER SCHEMA public OWNER TO postgres;

--
-- TOC entry 5057 (class 0 OID 0)
-- Dependencies: 5
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS '';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 219 (class 1259 OID 16542)
-- Name: activities; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.activities (
    activity_id integer NOT NULL,
    org_id integer,
    created_by integer,
    name character varying(150) NOT NULL,
    description text,
    academic_year character varying(10),
    semester character varying(10),
    date_started date,
    date_ended date,
    sdg_relation character varying(200),
    created_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.activities OWNER TO postgres;

--
-- TOC entry 220 (class 1259 OID 16550)
-- Name: activities_activity_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.activities_activity_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.activities_activity_id_seq OWNER TO postgres;

--
-- TOC entry 5059 (class 0 OID 0)
-- Dependencies: 220
-- Name: activities_activity_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.activities_activity_id_seq OWNED BY public.activities.activity_id;


--
-- TOC entry 221 (class 1259 OID 16551)
-- Name: documents; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.documents (
    document_id integer NOT NULL,
    org_id integer,
    activity_id integer,
    document_name character varying(150) NOT NULL,
    document_type character varying(100),
    academic_year integer,
    semester character varying(10),
    visibility character varying(20) DEFAULT 'restricted'::character varying,
    uploaded_at timestamp without time zone DEFAULT now(),
    document_file_path character varying(255),
    uploaded_by integer
);


ALTER TABLE public.documents OWNER TO postgres;

--
-- TOC entry 222 (class 1259 OID 16558)
-- Name: documents_document_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.documents_document_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.documents_document_id_seq OWNER TO postgres;

--
-- TOC entry 5060 (class 0 OID 0)
-- Dependencies: 222
-- Name: documents_document_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.documents_document_id_seq OWNED BY public.documents.document_id;


--
-- TOC entry 223 (class 1259 OID 16559)
-- Name: organizations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.organizations (
    org_id integer NOT NULL,
    name character varying(150) NOT NULL,
    acronym character varying(20),
    institutional_email character varying(100),
    affiliation character varying(50),
    org_type character varying(50),
    status character varying(20) DEFAULT 'active'::character varying,
    year_established integer,
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.organizations OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 16566)
-- Name: organizations_org_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.organizations_org_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.organizations_org_id_seq OWNER TO postgres;

--
-- TOC entry 5061 (class 0 OID 0)
-- Dependencies: 224
-- Name: organizations_org_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.organizations_org_id_seq OWNED BY public.organizations.org_id;


--
-- TOC entry 225 (class 1259 OID 16567)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    user_id integer NOT NULL,
    org_id integer,
    name character varying(100) NOT NULL,
    email character varying(100) NOT NULL,
    password text NOT NULL,
    role character varying(50) DEFAULT 'student'::character varying
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 16577)
-- Name: users_user_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_user_id_seq OWNER TO postgres;

--
-- TOC entry 5062 (class 0 OID 0)
-- Dependencies: 226
-- Name: users_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_user_id_seq OWNED BY public.users.user_id;


--
-- TOC entry 4871 (class 2604 OID 16578)
-- Name: activities activity_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activities ALTER COLUMN activity_id SET DEFAULT nextval('public.activities_activity_id_seq'::regclass);


--
-- TOC entry 4873 (class 2604 OID 16579)
-- Name: documents document_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documents ALTER COLUMN document_id SET DEFAULT nextval('public.documents_document_id_seq'::regclass);


--
-- TOC entry 4876 (class 2604 OID 16580)
-- Name: organizations org_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.organizations ALTER COLUMN org_id SET DEFAULT nextval('public.organizations_org_id_seq'::regclass);


--
-- TOC entry 4879 (class 2604 OID 16581)
-- Name: users user_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN user_id SET DEFAULT nextval('public.users_user_id_seq'::regclass);


--
-- TOC entry 5044 (class 0 OID 16542)
-- Dependencies: 219
-- Data for Name: activities; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.activities (activity_id, org_id, created_by, name, description, academic_year, semester, date_started, date_ended, sdg_relation, created_at) FROM stdin;
1	1	6	Tree Planting Drive	Environmental activity for sustainability.	2024-2025	1st	2024-08-10	2024-08-10	SDG 15: Life on Land	2025-11-06 23:55:52.641315
2	1	7	Zero Waste Seminar	Seminar on reducing campus waste.	2024-2025	2nd	2025-02-14	2025-02-14	SDG 12: Responsible Consumption	2025-11-06 23:55:52.641315
3	2	9	ICON Leadership Camp	Leadership training and team building.	2024-2025	1st	2024-09-20	2024-09-22	SDG 4: Quality Education	2025-11-06 23:55:52.641315
4	3	11	Financial Literacy Forum	Talk on personal finance for students.	2024-2025	2nd	2025-01-10	2025-01-10	SDG 8: Decent Work	2025-11-06 23:55:52.641315
5	3	12	Entrepreneurship Workshop	Hands-on business skills activity.	2024-2025	2nd	2025-03-03	2025-03-04	SDG 9: Industry Innovation	2025-11-06 23:55:52.641315
6	4	13	JPIA Accounting Quiz Bee	Academic competition on accounting topics.	2024-2025	1st	2024-10-05	2024-10-05	SDG 4: Quality Education	2025-11-06 23:55:52.641315
7	5	15	LIGHT Tourism Week	Week-long celebration of hospitality management.	2024-2025	1st	2024-09-25	2024-09-30	SDG 8: Decent Work	2025-11-06 23:55:52.641315
8	6	17	Marketing Seminar 2025	Lecture on digital marketing trends.	2024-2025	2nd	2025-02-20	2025-02-20	SDG 8: Decent Work	2025-11-06 23:55:52.641315
9	7	19	RPG Film Festival	Showcase of student-made short films.	2024-2025	1st	2024-11-15	2024-11-17	SDG 4: Quality Education	2025-11-06 23:55:52.641315
10	8	21	SCHEMA Code Jam	Programming competition for SAMCIS students.	2024-2025	1st	2024-09-05	2024-09-06	SDG 9: Innovation and Infrastructure	2025-11-06 23:55:52.641315
11	1	6	Tree Planting Drive (Batch 2)	Environmental activity for sustainability.	2024-2025	1st	2024-08-10	2024-08-10	SDG 15: Life on Land	2025-11-06 23:55:52.641315
12	1	7	Zero Waste Seminar (Batch 2)	Seminar on reducing campus waste.	2024-2025	2nd	2025-02-14	2025-02-14	SDG 12: Responsible Consumption	2025-11-06 23:55:52.641315
13	2	9	ICON Leadership Camp (Batch 2)	Leadership training and team building.	2024-2025	1st	2024-09-20	2024-09-22	SDG 4: Quality Education	2025-11-06 23:55:52.641315
14	3	11	Financial Literacy Forum (Batch 2)	Talk on personal finance for students.	2024-2025	2nd	2025-01-10	2025-01-10	SDG 8: Decent Work	2025-11-06 23:55:52.641315
15	3	12	Entrepreneurship Workshop (Batch 2)	Hands-on business skills activity.	2024-2025	2nd	2025-03-03	2025-03-04	SDG 9: Industry Innovation	2025-11-06 23:55:52.641315
16	4	13	JPIA Accounting Quiz Bee (Batch 2)	Academic competition on accounting topics.	2024-2025	1st	2024-10-05	2024-10-05	SDG 4: Quality Education	2025-11-06 23:55:52.641315
17	5	15	LIGHT Tourism Week (Batch 2)	Week-long celebration of hospitality management.	2024-2025	1st	2024-09-25	2024-09-30	SDG 8: Decent Work	2025-11-06 23:55:52.641315
18	6	17	Marketing Seminar 2025 (Batch 2)	Lecture on digital marketing trends.	2024-2025	2nd	2025-02-20	2025-02-20	SDG 8: Decent Work	2025-11-06 23:55:52.641315
19	7	19	RPG Film Festival (Batch 2)	Showcase of student-made short films.	2024-2025	1st	2024-11-15	2024-11-17	SDG 4: Quality Education	2025-11-06 23:55:52.641315
20	8	21	SCHEMA Code Jam (Batch 2)	Programming competition for SAMCIS students.	2024-2025	1st	2024-09-05	2024-09-06	SDG 9: Innovation and Infrastructure	2025-11-06 23:55:52.641315
\.


--
-- TOC entry 5046 (class 0 OID 16551)
-- Dependencies: 221
-- Data for Name: documents; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.documents (document_id, org_id, activity_id, document_name, document_type, academic_year, semester, visibility, uploaded_at, document_file_path, uploaded_by) FROM stdin;
1	1	1	Tree Planting Drive Report	Activity Report	2024	1st	public	2025-11-06 23:55:52.641315	\N	\N
2	1	2	Zero Waste Seminar Report	Activity Report	2024	1st	restricted	2025-11-06 23:55:52.641315	\N	\N
3	2	3	ICON Leadership Camp Report	Activity Report	2024	1st	public	2025-11-06 23:55:52.641315	\N	\N
4	3	4	Financial Literacy Forum Report	Activity Report	2024	1st	restricted	2025-11-06 23:55:52.641315	\N	\N
5	3	5	Entrepreneurship Workshop Report	Activity Report	2024	1st	public	2025-11-06 23:55:52.641315	\N	\N
6	4	6	JPIA Accounting Quiz Bee Report	Activity Report	2024	1st	restricted	2025-11-06 23:55:52.641315	\N	\N
7	5	7	LIGHT Tourism Week Report	Activity Report	2024	1st	public	2025-11-06 23:55:52.641315	\N	\N
8	6	8	Marketing Seminar 2025 Report	Activity Report	2024	1st	restricted	2025-11-06 23:55:52.641315	\N	\N
9	7	9	RPG Film Festival Report	Activity Report	2024	1st	public	2025-11-06 23:55:52.641315	\N	\N
10	8	10	SCHEMA Code Jam Report	Activity Report	2024	1st	restricted	2025-11-06 23:55:52.641315	\N	\N
11	1	11	Tree Planting Drive (Batch 2) Report	Activity Report	2024	1st	public	2025-11-06 23:55:52.641315	\N	\N
12	1	12	Zero Waste Seminar (Batch 2) Report	Activity Report	2024	1st	restricted	2025-11-06 23:55:52.641315	\N	\N
13	2	13	ICON Leadership Camp (Batch 2) Report	Activity Report	2024	1st	public	2025-11-06 23:55:52.641315	\N	\N
14	3	14	Financial Literacy Forum (Batch 2) Report	Activity Report	2024	1st	restricted	2025-11-06 23:55:52.641315	\N	\N
15	3	15	Entrepreneurship Workshop (Batch 2) Report	Activity Report	2024	1st	public	2025-11-06 23:55:52.641315	\N	\N
16	4	16	JPIA Accounting Quiz Bee (Batch 2) Report	Activity Report	2024	1st	restricted	2025-11-06 23:55:52.641315	\N	\N
17	5	17	LIGHT Tourism Week (Batch 2) Report	Activity Report	2024	1st	public	2025-11-06 23:55:52.641315	\N	\N
18	6	18	Marketing Seminar 2025 (Batch 2) Report	Activity Report	2024	1st	restricted	2025-11-06 23:55:52.641315	\N	\N
19	7	19	RPG Film Festival (Batch 2) Report	Activity Report	2024	1st	public	2025-11-06 23:55:52.641315	\N	\N
20	8	20	SCHEMA Code Jam (Batch 2) Report	Activity Report	2024	1st	restricted	2025-11-06 23:55:52.641315	\N	\N
21	1	1	Untitled document	application/pdf	\N	\N	restricted	2025-12-12 00:26:17.381183	doc1.pdf	6
\.


--
-- TOC entry 5048 (class 0 OID 16559)
-- Dependencies: 223
-- Data for Name: organizations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.organizations (org_id, name, acronym, institutional_email, affiliation, org_type, status, year_established, updated_at) FROM stdin;
1	Green Core Society	GCS	gcssamcis@slu.edu.ph	SAMCIS	Co-Curricular	active	2015	2025-11-06 23:55:52.641315
2	Integrated Confederacy	ICON	iconsamcis@slu.edu.ph	SAMCIS	Co-Curricular	active	2012	2025-11-06 23:55:52.641315
3	Junior Financial Executives of the Philippines - SLU Chapter	JFINEX	jrfinexsamcis@slu.edu.ph	SAMCIS	Co-Curricular	active	2010	2025-11-06 23:55:52.641315
4	Junior Philippine Institute of Accountants	JPIA	jpiasamcis@slu.edu.ph	SAMCIS	Co-Curricular	active	2011	2025-11-06 23:55:52.641315
5	Louisians Imbibed with Genuine Spirit for Hospitality Transformation	LIGHT	lightsamcis@slu.edu.ph	SAMCIS	Extra-Curricular	active	2016	2025-11-06 23:55:52.641315
6	Marketing Mixers	MM	mmsamcis@slu.edu.ph	SAMCIS	Co-Curricular	active	2013	2025-11-06 23:55:52.641315
7	Rated: Production Guild	RPG	rpgsamcis@slu.edu.ph	SAMCIS	Extra-Curricular	active	2014	2025-11-06 23:55:52.641315
8	SCHEMA	SCHEMA	schemasamcis@slu.edu.ph	SAMCIS	Co-Curricular	active	2015	2025-11-06 23:55:52.641315
9	Society of Integrated Commercians for Academic Progress	SICAP	sicapsamcis@slu.edu.ph	SAMCIS	Co-Curricular	active	2012	2025-11-06 23:55:52.641315
10	Young Entrepreneurs’ Society	YES	yessamcis@slu.edu.ph	SAMCIS	Co-Curricular	active	2017	2025-11-06 23:55:52.641315
\.


--
-- TOC entry 5050 (class 0 OID 16567)
-- Dependencies: 225
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (user_id, org_id, name, email, password, role) FROM stdin;
1	\N	Dr. Liza Torres	osas_admin1@slu.edu.ph	osas123	osas
2	\N	Engr. Paul Ramos	osas_admin2@slu.edu.ph	osas123	osas
3	\N	Mr. Jake Lim	osas_admin3@slu.edu.ph	osas123	osas
4	\N	Ms. Kim Austria	osas_admin4@slu.edu.ph	osas123	osas
5	\N	Dr. John F. Dizon	osas_admin5@slu.edu.ph	osas123	osas
6	1	Angela Bautista	angela_gcs@slu.edu.ph	12345	student
7	1	Prof. Mariel Santos	mariel_gcs@slu.edu.ph	12345	adviser
8	2	Carlos Enriquez	carlos_icon@slu.edu.ph	12345	student
9	2	Prof. Tricia Go	tricia_icon@slu.edu.ph	12345	adviser
10	3	Rina Perez	rina_jfinex@slu.edu.ph	12345	student
11	3	Prof. Noel Cruz	noel_jfinex@slu.edu.ph	12345	adviser
12	4	James Salvador	james_jpia@slu.edu.ph	12345	student
13	4	Prof. Carla De Leon	carla_jpia@slu.edu.ph	12345	adviser
14	5	Diana Villanueva	diana_light@slu.edu.ph	12345	student
15	5	Prof. Julio Magsino	julio_light@slu.edu.ph	12345	adviser
16	6	Martin Garcia	martin_mm@slu.edu.ph	12345	student
17	6	Prof. Elaine Robles	elaine_mm@slu.edu.ph	12345	adviser
18	7	Rica Del Rosario	rica_rpg@slu.edu.ph	12345	student
19	7	Prof. David Roldan	david_rpg@slu.edu.ph	12345	adviser
20	8	Lara Mendoza	lara_schema@slu.edu.ph	12345	student
21	8	Prof. Benj Navarro	benj_schema@slu.edu.ph	12345	adviser
22	9	Nico Agustin	nico_sicap@slu.edu.ph	12345	student
23	9	Prof. Cathy Uy	cathy_sicap@slu.edu.ph	12345	adviser
24	10	Ellaine Ramos	ellaine_yes@slu.edu.ph	12345	student
25	10	Prof. Arvin Lee	arvin_yes@slu.edu.ph	12345	adviser
26	1	Miguel Dela Cruz	miguel_gcs@slu.edu.ph	12345	student
27	1	Sophia Reyes	sophia_gcs@slu.edu.ph	12345	student
28	3	Jacob Tan	jacob_jfinex@slu.edu.ph	12345	student
29	3	Jenny Manalo	jenny_jfinex@slu.edu.ph	12345	student
30	4	Ralph Ong	ralph_jpia@slu.edu.ph	12345	student
31	5	Angela Torralba	angela_light@slu.edu.ph	12345	student
32	6	Chris Bautista	chris_mm@slu.edu.ph	12345	student
33	7	Paula Rivera	paula_rpg@slu.edu.ph	12345	student
34	8	Miko Santos	miko_schema@slu.edu.ph	12345	student
35	9	Bea Laya	bea_sicap@slu.edu.ph	12345	student
36	10	Mark Villamor	mark_yes@slu.edu.ph	12345	student
37	\N	Ecrnip Francisco	whiteboi@slu.edu.ph	admin123	admin
38	\N	Lebron Supang	goat@slu.edu.ph	admin123	admin
\.


--
-- TOC entry 5063 (class 0 OID 0)
-- Dependencies: 220
-- Name: activities_activity_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.activities_activity_id_seq', 20, true);


--
-- TOC entry 5064 (class 0 OID 0)
-- Dependencies: 222
-- Name: documents_document_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.documents_document_id_seq', 21, true);


--
-- TOC entry 5065 (class 0 OID 0)
-- Dependencies: 224
-- Name: organizations_org_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.organizations_org_id_seq', 10, true);


--
-- TOC entry 5066 (class 0 OID 0)
-- Dependencies: 226
-- Name: users_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_user_id_seq', 36, true);


--
-- TOC entry 4882 (class 2606 OID 16583)
-- Name: activities activities_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT activities_pkey PRIMARY KEY (activity_id);


--
-- TOC entry 4884 (class 2606 OID 16585)
-- Name: documents documents_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_pkey PRIMARY KEY (document_id);


--
-- TOC entry 4886 (class 2606 OID 16587)
-- Name: organizations organizations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.organizations
    ADD CONSTRAINT organizations_pkey PRIMARY KEY (org_id);


--
-- TOC entry 4888 (class 2606 OID 16589)
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- TOC entry 4890 (class 2606 OID 16591)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (user_id);


--
-- TOC entry 4891 (class 2606 OID 16592)
-- Name: activities activities_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT activities_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- TOC entry 4892 (class 2606 OID 16597)
-- Name: activities activities_org_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT activities_org_id_fkey FOREIGN KEY (org_id) REFERENCES public.organizations(org_id) ON DELETE CASCADE;


--
-- TOC entry 4893 (class 2606 OID 16602)
-- Name: documents documents_activity_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_activity_id_fkey FOREIGN KEY (activity_id) REFERENCES public.activities(activity_id) ON DELETE CASCADE;


--
-- TOC entry 4894 (class 2606 OID 16607)
-- Name: documents documents_org_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_org_id_fkey FOREIGN KEY (org_id) REFERENCES public.organizations(org_id) ON DELETE CASCADE;


--
-- TOC entry 4895 (class 2606 OID 16619)
-- Name: documents documents_uploaded_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_uploaded_by_fkey FOREIGN KEY (uploaded_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- TOC entry 4896 (class 2606 OID 16612)
-- Name: users users_org_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_org_id_fkey FOREIGN KEY (org_id) REFERENCES public.organizations(org_id) ON DELETE SET NULL;


--
-- TOC entry 5058 (class 0 OID 0)
-- Dependencies: 5
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;


-- Completed on 2025-12-12 00:55:14

--
-- PostgreSQL database dump complete
--

\unrestrict GSKlkaNOJa1TAHZsygS7RcyhGE6T5meBnUwhkWux5QStWBj4QI17GaDra9bhudP


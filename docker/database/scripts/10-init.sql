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

\connect question_service

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
-- Name: notify_messenger_messages(); Type: FUNCTION; Schema: public; Owner: question_service
--

CREATE FUNCTION public.notify_messenger_messages() RETURNS trigger
    LANGUAGE plpgsql
AS $$
BEGIN
    PERFORM pg_notify('messenger_messages', NEW.queue_name::text);
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.notify_messenger_messages() OWNER TO question_service;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: doctrine_migration_versions; Type: TABLE; Schema: public; Owner: question_service
--

CREATE TABLE public.doctrine_migration_versions (
                                                    version character varying(191) NOT NULL,
                                                    executed_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                                    execution_time integer
);


ALTER TABLE public.doctrine_migration_versions OWNER TO question_service;

--
-- Name: messenger_messages; Type: TABLE; Schema: public; Owner: question_service
--

CREATE TABLE public.messenger_messages (
                                           id bigint NOT NULL,
                                           body text NOT NULL,
                                           headers text NOT NULL,
                                           queue_name character varying(190) NOT NULL,
                                           created_at timestamp(0) without time zone NOT NULL,
                                           available_at timestamp(0) without time zone NOT NULL,
                                           delivered_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone
);


ALTER TABLE public.messenger_messages OWNER TO question_service;

--
-- Name: messenger_messages_id_seq; Type: SEQUENCE; Schema: public; Owner: question_service
--

CREATE SEQUENCE public.messenger_messages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.messenger_messages_id_seq OWNER TO question_service;

--
-- Name: messenger_messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: question_service
--

ALTER SEQUENCE public.messenger_messages_id_seq OWNED BY public.messenger_messages.id;


--
-- Name: question; Type: TABLE; Schema: public; Owner: question_service
--

CREATE TABLE public.question (
                                 id integer NOT NULL,
                                 user_id integer,
                                 category_id integer NOT NULL,
                                 status character varying(20) NOT NULL,
                                 title text NOT NULL,
                                 text text,
                                 slug character varying(200) NOT NULL,
                                 href character varying(250),
                                 created_by_ip character varying(46),
                                 total_answers integer NOT NULL,
                                 created_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                 updated_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone
);


ALTER TABLE public.question OWNER TO question_service;

--
-- Name: question_answer; Type: TABLE; Schema: public; Owner: question_service
--

CREATE TABLE public.question_answer (
                                        id integer NOT NULL,
                                        user_id integer,
                                        question_id integer NOT NULL,
                                        status character varying(20) NOT NULL,
                                        text text NOT NULL,
                                        created_by_ip character varying(46),
                                        created_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                        updated_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone
);


ALTER TABLE public.question_answer OWNER TO question_service;

--
-- Name: question_answer_id_seq; Type: SEQUENCE; Schema: public; Owner: question_service
--

CREATE SEQUENCE public.question_answer_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.question_answer_id_seq OWNER TO question_service;

--
-- Name: question_category; Type: TABLE; Schema: public; Owner: question_service
--

CREATE TABLE public.question_category (
                                          id integer NOT NULL,
                                          status character varying(20) NOT NULL,
                                          title text NOT NULL,
                                          slug character varying(200) NOT NULL,
                                          href character varying(250),
                                          total_questions integer NOT NULL,
                                          created_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                          updated_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                          description text
);


ALTER TABLE public.question_category OWNER TO question_service;

--
-- Name: question_category_id_seq; Type: SEQUENCE; Schema: public; Owner: question_service
--

CREATE SEQUENCE public.question_category_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.question_category_id_seq OWNER TO question_service;

--
-- Name: question_id_seq; Type: SEQUENCE; Schema: public; Owner: question_service
--

CREATE SEQUENCE public.question_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.question_id_seq OWNER TO question_service;

--
-- Name: sessions; Type: TABLE; Schema: public; Owner: question_service
--

CREATE TABLE public.sessions (
                                 sess_id character varying(128) NOT NULL,
                                 sess_data bytea NOT NULL,
                                 sess_lifetime integer NOT NULL,
                                 sess_time integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO question_service;

--
-- Name: user; Type: TABLE; Schema: public; Owner: question_service
--

CREATE TABLE public."user" (
                               id integer NOT NULL,
                               status character varying(20) NOT NULL,
                               username character varying(200) NOT NULL,
                               email character varying(200) NOT NULL,
                               email_verified boolean NOT NULL,
                               email_subscribed boolean NOT NULL,
                               roles jsonb NOT NULL,
                               password character varying(255) NOT NULL,
                               created_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                               updated_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                               email_verified_token character varying(100),
                               email_subscribed_token character varying(100),
                               password_restore_token character varying(100) DEFAULT NULL::character varying,
                               photo_id integer,
                               about text
);


ALTER TABLE public."user" OWNER TO question_service;

--
-- Name: user_id_seq; Type: SEQUENCE; Schema: public; Owner: question_service
--

CREATE SEQUENCE public.user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.user_id_seq OWNER TO question_service;

--
-- Name: user_photo; Type: TABLE; Schema: public; Owner: question_service
--

CREATE TABLE public.user_photo (
                                   id integer NOT NULL,
                                   user_id integer NOT NULL,
                                   status character varying(20) NOT NULL,
                                   original_path text NOT NULL,
                                   thumbnail_path text,
                                   created_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                   updated_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone
);


ALTER TABLE public.user_photo OWNER TO question_service;

--
-- Name: user_photo_id_seq; Type: SEQUENCE; Schema: public; Owner: question_service
--

CREATE SEQUENCE public.user_photo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.user_photo_id_seq OWNER TO question_service;

--
-- Name: messenger_messages id; Type: DEFAULT; Schema: public; Owner: question_service
--

ALTER TABLE ONLY public.messenger_messages ALTER COLUMN id SET DEFAULT nextval('public.messenger_messages_id_seq'::regclass);


--
-- Data for Name: doctrine_migration_versions; Type: TABLE DATA; Schema: public; Owner: question_service
--

INSERT INTO public.doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20210121182450', '2021-01-21 21:28:01', 14);
INSERT INTO public.doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20210122125054', '2021-01-22 15:56:16', 12);
INSERT INTO public.doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20210123080146', '2021-01-23 11:03:57', 21);
INSERT INTO public.doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20210123125235', '2021-01-23 15:53:46', 14);
INSERT INTO public.doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20210125072933', '2021-01-25 10:49:48', 15);
INSERT INTO public.doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20210128073702', '2021-01-28 10:39:20', 28);
INSERT INTO public.doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20210128082911', '2021-01-28 11:30:28', 14);
INSERT INTO public.doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20210128093024', '2021-01-28 12:31:15', 16);
INSERT INTO public.doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20210128130720', '2021-01-28 16:07:31', 19);
INSERT INTO public.doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20210201055045', '2021-02-01 08:51:43', 95);
INSERT INTO public.doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20210202092112', '2021-02-02 12:23:33', 12);
INSERT INTO public.doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20210205065104', '2021-02-05 09:51:50', 13);

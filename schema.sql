--
-- PostgreSQL database dump
--

-- Dumped from database version 17.4
-- Dumped by pg_dump version 17.4

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

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: categories; Type: TABLE; Schema: public; Owner: store_user
--

CREATE TABLE public.categories (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    description text,
    created_at timestamp with time zone DEFAULT now(),
    name_old text,
    description_old text
);


ALTER TABLE public.categories OWNER TO store_user;

--
-- Name: categories_id_seq; Type: SEQUENCE; Schema: public; Owner: store_user
--

CREATE SEQUENCE public.categories_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categories_id_seq OWNER TO store_user;

--
-- Name: categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: store_user
--

ALTER SEQUENCE public.categories_id_seq OWNED BY public.categories.id;


--
-- Name: coupons; Type: TABLE; Schema: public; Owner: store_user
--

CREATE TABLE public.coupons (
    id integer NOT NULL,
    code character varying(50) NOT NULL,
    discount_percent numeric(5,2) NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    expires_at timestamp without time zone,
    CONSTRAINT coupons_discount_percent_check CHECK (((discount_percent > (0)::numeric) AND (discount_percent <= (100)::numeric)))
);


ALTER TABLE public.coupons OWNER TO store_user;

--
-- Name: coupons_id_seq; Type: SEQUENCE; Schema: public; Owner: store_user
--

CREATE SEQUENCE public.coupons_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.coupons_id_seq OWNER TO store_user;

--
-- Name: coupons_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: store_user
--

ALTER SEQUENCE public.coupons_id_seq OWNED BY public.coupons.id;


--
-- Name: order_items; Type: TABLE; Schema: public; Owner: store_user
--

CREATE TABLE public.order_items (
    id integer NOT NULL,
    order_id integer NOT NULL,
    product_id integer NOT NULL,
    quantity integer DEFAULT 1 NOT NULL,
    unit_price numeric(10,2) NOT NULL
);


ALTER TABLE public.order_items OWNER TO store_user;

--
-- Name: order_items_id_seq; Type: SEQUENCE; Schema: public; Owner: store_user
--

CREATE SEQUENCE public.order_items_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.order_items_id_seq OWNER TO store_user;

--
-- Name: order_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: store_user
--

ALTER SEQUENCE public.order_items_id_seq OWNED BY public.order_items.id;


--
-- Name: orders; Type: TABLE; Schema: public; Owner: store_user
--

CREATE TABLE public.orders (
    id integer NOT NULL,
    user_id integer,
    client_name character varying(100) NOT NULL,
    phone character varying(20) NOT NULL,
    delivery_address text NOT NULL,
    status character varying(20) DEFAULT 'new'::character varying NOT NULL,
    created_at timestamp with time zone DEFAULT now(),
    payment_method character varying(20) DEFAULT 'cod'::character varying NOT NULL,
    payment_status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    payment_external_id character varying(64),
    total_amount numeric(10,2) DEFAULT 0 NOT NULL,
    paid_at timestamp with time zone,
    coupon_code character varying(50),
    discount_amount numeric(10,2) DEFAULT 0 NOT NULL
);


ALTER TABLE public.orders OWNER TO store_user;

--
-- Name: orders_id_seq; Type: SEQUENCE; Schema: public; Owner: store_user
--

CREATE SEQUENCE public.orders_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.orders_id_seq OWNER TO store_user;

--
-- Name: orders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: store_user
--

ALTER SEQUENCE public.orders_id_seq OWNED BY public.orders.id;


--
-- Name: products; Type: TABLE; Schema: public; Owner: store_user
--

CREATE TABLE public.products (
    id integer NOT NULL,
    category_id integer NOT NULL,
    name character varying(150) NOT NULL,
    description text,
    price numeric(10,2) NOT NULL,
    stock integer DEFAULT 0,
    image_url text,
    created_at timestamp with time zone DEFAULT now(),
    name_old text,
    description_old text
);


ALTER TABLE public.products OWNER TO store_user;

--
-- Name: products_id_seq; Type: SEQUENCE; Schema: public; Owner: store_user
--

CREATE SEQUENCE public.products_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.products_id_seq OWNER TO store_user;

--
-- Name: products_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: store_user
--

ALTER SEQUENCE public.products_id_seq OWNED BY public.products.id;


--
-- Name: sbp_payments; Type: TABLE; Schema: public; Owner: store_user
--

CREATE TABLE public.sbp_payments (
    id integer NOT NULL,
    external_id character varying(64) NOT NULL,
    order_id integer NOT NULL,
    amount numeric(10,2) NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL
);


ALTER TABLE public.sbp_payments OWNER TO store_user;

--
-- Name: sbp_payments_id_seq; Type: SEQUENCE; Schema: public; Owner: store_user
--

CREATE SEQUENCE public.sbp_payments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sbp_payments_id_seq OWNER TO store_user;

--
-- Name: sbp_payments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: store_user
--

ALTER SEQUENCE public.sbp_payments_id_seq OWNED BY public.sbp_payments.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: store_user
--

CREATE TABLE public.users (
    id integer NOT NULL,
    email character varying(255) NOT NULL,
    password_hash character varying(255) NOT NULL,
    name character varying(100),
    is_admin boolean DEFAULT false,
    created_at timestamp with time zone DEFAULT now()
);


ALTER TABLE public.users OWNER TO store_user;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: store_user
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO store_user;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: store_user
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: categories id; Type: DEFAULT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.categories ALTER COLUMN id SET DEFAULT nextval('public.categories_id_seq'::regclass);


--
-- Name: coupons id; Type: DEFAULT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.coupons ALTER COLUMN id SET DEFAULT nextval('public.coupons_id_seq'::regclass);


--
-- Name: order_items id; Type: DEFAULT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.order_items ALTER COLUMN id SET DEFAULT nextval('public.order_items_id_seq'::regclass);


--
-- Name: orders id; Type: DEFAULT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.orders ALTER COLUMN id SET DEFAULT nextval('public.orders_id_seq'::regclass);


--
-- Name: products id; Type: DEFAULT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.products ALTER COLUMN id SET DEFAULT nextval('public.products_id_seq'::regclass);


--
-- Name: sbp_payments id; Type: DEFAULT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.sbp_payments ALTER COLUMN id SET DEFAULT nextval('public.sbp_payments_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: categories; Type: TABLE DATA; Schema: public; Owner: store_user
--

COPY public.categories (id, name, description, created_at, name_old, description_old) FROM stdin;
1	Гитары	Различные типы гитар	2025-06-08 02:34:59.86003+03	\N	\N
2	Флейты	Деревянные и металлические	2025-06-08 02:34:59.86003+03	\N	\N
3	Скрипки	От классических до электрических	2025-06-08 02:34:59.86003+03	\N	\N
4	Укулеле	Перечень - Сопрано-укулеле, Концертное укулеле, Тенор-укулеле, Баритон-укулеле	2025-06-08 05:41:40.503165+03	\N	\N
5	Саксофон	От ученического до профессионального	2025-06-08 06:18:30.32856+03	\N	\N
\.


--
-- Data for Name: coupons; Type: TABLE DATA; Schema: public; Owner: store_user
--

COPY public.coupons (id, code, discount_percent, is_active, expires_at) FROM stdin;
2	BLACK10	10.00	t	2025-12-05 23:59:59
1	WELCOME15	15.00	f	\N
\.


--
-- Data for Name: order_items; Type: TABLE DATA; Schema: public; Owner: store_user
--

COPY public.order_items (id, order_id, product_id, quantity, unit_price) FROM stdin;
1	2	2	1	55000.00
2	3	7	1	19500.00
3	4	6	1	31000.00
4	5	6	1	31000.00
5	6	6	1	31000.00
6	7	4	1	25000.00
7	8	4	1	25000.00
8	9	2	1	55000.00
9	9	1	1	12000.00
10	9	6	1	31000.00
11	9	7	2	19500.00
12	9	5	1	9500.00
13	9	3	1	12000.00
14	10	6	1	31000.00
15	11	6	1	31000.00
16	12	7	1	19500.00
17	13	4	1	25000.00
18	14	7	1	19500.00
19	15	4	1	25000.00
20	16	5	1	9500.00
21	17	7	1	19500.00
22	17	6	1	31000.00
23	17	5	1	9500.00
24	18	7	1	19500.00
25	19	4	1	25000.00
26	20	6	1	31000.00
27	21	5	1	9500.00
28	22	6	1	31000.00
29	23	2	1	55000.00
30	24	7	1	19500.00
31	25	3	1	12000.00
32	26	7	1	19500.00
33	27	1	1	12000.00
34	28	6	1	31000.00
35	29	7	1	19500.00
36	30	5	1	9500.00
37	31	7	1	19500.00
38	32	5	1	9500.00
39	33	7	1	19500.00
40	34	7	1	19500.00
41	35	4	1	25000.00
42	36	7	1	19500.00
43	37	6	1	31000.00
\.


--
-- Data for Name: orders; Type: TABLE DATA; Schema: public; Owner: store_user
--

COPY public.orders (id, user_id, client_name, phone, delivery_address, status, created_at, payment_method, payment_status, payment_external_id, total_amount, paid_at, coupon_code, discount_amount) FROM stdin;
1	1	Александр	+7 (903) 111-02-37	Улица Пушкина. Дом Колотушкина	cancelled	2025-06-08 04:43:57.41903+03	cod	pending	\N	0.00	\N	\N	0.00
2	1	Александр	+7 (903) 111-02-37	Улица Пушкина. Дом Колотушкина	processing	2025-06-08 04:45:56.638038+03	cod	pending	\N	0.00	\N	\N	0.00
3	1	Александр	+7 (903) 111-02-37	Улица Пушкина. Дом Колотушкина	processing	2025-06-08 11:21:04.369372+03	cod	pending	\N	0.00	\N	\N	0.00
4	1	Александр	+7 (903) 111-02-37	Москва Зеленоград	new	2025-06-08 11:50:44.254987+03	cod	pending	\N	0.00	\N	\N	0.00
21	4	Михаил	9031110237	г Москва, ул Барышиха, д. 25, к. 1\r\n125368	delivered	2025-12-02 20:11:14.393083+03	cod	paid	\N	9500.00	2025-12-02 20:44:39+03	\N	0.00
8	4	Михаил	+7 (903) 111-02-37	Москва	delivered	2025-06-08 12:06:29.899533+03	cod	pending	\N	0.00	\N	\N	0.00
12	4	Михаил	+7 (903) 111-02-37	rsgdf	new	2025-06-08 13:46:13.226359+03	cod	pending	\N	0.00	\N	\N	0.00
13	5	Михаил	+7 (903) 111-02-37	Туда	new	2025-06-08 22:49:23.583011+03	cod	pending	\N	0.00	\N	\N	0.00
15	6	Михаил	+7 (903) 111-02-37	Туда	processing	2025-06-08 22:51:37.770279+03	cod	pending	\N	0.00	\N	\N	0.00
16	6	Михаил	+7 (903) 111-02-37	nelf	new	2025-06-08 23:13:08.822234+03	cod	pending	\N	0.00	\N	\N	0.00
18	4	Михаил	9031110237	г Москва, ул Барышиха, д. 25, к. 1\r\n125368	new	2025-12-02 19:54:25.526593+03	cod	pending	\N	19500.00	\N	\N	0.00
22	4	Михаил	9031110237	г Москва, ул Барышиха, д. 25, к. 1\r\n125368	cancelled	2025-12-02 20:17:56.666595+03	cod	pending	\N	31000.00	\N	\N	0.00
20	4	Михаил	9031110237	г Москва, ул Барышиха, д. 25, к. 1\r\n125368	cancelled	2025-12-02 20:07:13.559436+03	cod	pending	\N	31000.00	\N	\N	0.00
19	4	Михаил	9031110237	г Москва, ул Барышиха, д. 25, к. 1\r\n125368	cancelled	2025-12-02 20:03:41.443107+03	cod	pending	\N	25000.00	\N	\N	0.00
10	3	Михаил	+7 (903) 111-02-37	Москва Зеленоград	delivered	2025-06-08 13:45:05.614458+03	cod	pending	\N	0.00	\N	\N	0.00
26	3	Толик	9055378277	г Москва, ул Барышиха, д. 25, к. 1	cancelled	2025-12-03 01:09:31.328201+03	sbp	pending	SBP-e9fd545b3d1a82df	19500.00	\N	\N	0.00
33	3	Бобик	9031110237	г Москва, ул Барышиха, д. 25, к. 1	new	2025-12-03 03:38:28.07626+03	sbp	paid	SBP-b96b0934a5b593f9	16575.00	2025-12-03 00:38:40+03	\N	0.00
6	1	Александр	+7 (903) 111-02-37	туда	cancelled	2025-06-08 12:00:28.974951+03	cod	pending	\N	0.00	\N	\N	0.00
7	4	Михаил	+7 (903) 111-02-37	Москва	delivered	2025-06-08 12:06:08.229859+03	cod	pending	\N	0.00	\N	\N	0.00
5	1	Александр	+7 (903) 111-02-37	туда	shipped	2025-06-08 11:57:01.355669+03	cod	pending	\N	0.00	\N	\N	0.00
9	4	Михаил	+7 (903) 111-02-37	Москва	delivered	2025-06-08 12:15:59.999778+03	cod	pending	\N	0.00	\N	\N	0.00
14	5	Михаил	+7 (903) 111-02-37	Туда	shipped	2025-06-08 22:50:23.176505+03	cod	pending	\N	0.00	\N	\N	0.00
17	4	Michail	9031110237	г Москва, ул Барышиха, д. 25, к. 1\r\n125368	shipped	2025-12-02 19:47:50.023343+03	cod	pending	\N	60000.00	\N	\N	0.00
27	3	Толик	9671555294	г Москва, ул Барышиха, д. 25, к. 1\r\n125368	new	2025-12-03 01:12:45.146169+03	sbp	paid	SBP-5790e6102bc96859	12000.00	2025-12-02 22:13:15+03	\N	0.00
11	3	Михаил	+7 (903) 111-02-37	1	delivered	2025-06-08 13:45:41.65475+03	cod	paid	\N	0.00	2025-12-02 20:37:25+03	\N	0.00
23	4	Александр	9671555294	г Москва, ул Барышиха, д. 25, к. 1\r\n125368	delivered	2025-12-02 22:25:49.529381+03	sbp	paid	SBP-97f03f354f8d6be7	55000.00	2025-12-02 19:26:45+03	\N	0.00
24	3	Вова	9036191097	г Москва, ул Барышиха, д. 25, к. 1	shipped	2025-12-02 23:19:24.083637+03	sbp	paid	SBP-92342528e48ac0cb	19500.00	2025-12-02 20:20:36+03	\N	0.00
34	3	Александр	9137008513	г Москва, ул Барышиха, д. 25, к. 1	new	2025-12-03 17:39:01.816644+03	sbp	paid	SBP-258fa64d692391e7	19500.00	2025-12-03 14:39:14+03	\N	0.00
28	3	Болик	9137008513	г Москва, ул Барышиха, д. 25, к. 1\r\n125368	cancelled	2025-12-03 01:29:17.007377+03	sbp	pending	SBP-c92a8f4895fc2e66	31000.00	\N	\N	0.00
25	3	Ваня	9936075726	г Москва, ул Барышиха, д. 25, к. 1\r\n125368	cancelled	2025-12-03 00:55:51.890879+03	sbp	pending	SBP-f25ed85159929c4e	12000.00	\N	\N	0.00
29	3	Толик	9671555294	г Москва, ул Барышиха, д. 25, к. 1\r\n125368	new	2025-12-03 01:45:19.968312+03	sbp	pending	SBP-33de1d597e24bc6a	19500.00	\N	\N	0.00
30	3	Толик	9671555294	г Москва, ул Барышиха, д. 25, к. 1\r\n125368	new	2025-12-03 02:16:21.948979+03	sbp	pending	SBP-e3cac7efccfcfcd2	9500.00	\N	\N	0.00
31	3	Вова	9671555294	г Москва, ул Барышиха, д. 25, к. 1	new	2025-12-03 02:56:37.342636+03	sbp	pending	SBP-7148eb075c0dfafb	19500.00	\N	\N	0.00
35	3	Александр	9031110237	г Москва, ул Барышиха, д. 25, к. 1	new	2025-12-03 17:39:38.526188+03	cod	pending	\N	25000.00	\N	\N	0.00
32	3	Толик	9137008513	г Москва, ул Барышиха, д. 25, к. 1	new	2025-12-03 03:11:20.873291+03	sbp	paid	SBP-7633d2a9cb4050ef	8075.00	2025-12-03 00:11:28+03	\N	0.00
36	3	Толик	9036191097	г Москва, ул Барышиха, д. 25, к. 1	new	2025-12-03 19:12:41.445026+03	sbp	paid	SBP-6b24a35b66831cb1	16575.00	2025-12-03 16:14:56+03	\N	0.00
37	3	Вова	9671555294	г Москва, ул Барышиха, д. 25, к. 1	new	2025-12-03 19:21:14.359122+03	sbp	paid	SBP-28d88a76c583236e	31000.00	2025-12-03 16:21:33+03	\N	0.00
\.


--
-- Data for Name: products; Type: TABLE DATA; Schema: public; Owner: store_user
--

COPY public.products (id, category_id, name, description, price, stock, image_url, created_at, name_old, description_old) FROM stdin;
1	1	Акустическая гитара Yamaha F310	Надёжная бюджетная акустика	12000.00	5	/uploads/products/prod_6844faad3c8508.88886573.png	2025-06-08 02:34:59.862482+03	\N	\N
2	1	Электрогитара Fender Stratocaster	Легендарная модель	55000.00	2	/uploads/products/prod_6844fb23ad2204.11421027.png	2025-06-08 02:34:59.862482+03	\N	\N
3	2	Сопрано-флейта Gemeinhardt	Для начинающих и профи	12000.00	4	/uploads/products/prod_6844fbf36bad65.22374068.png	2025-06-08 02:34:59.862482+03	\N	\N
4	3	Классическая скрипка 4/4	Полный размер, с комплектом смычка	25000.00	6	/uploads/products/prod_6844fb62dbd125.59884764.png	2025-06-08 02:34:59.862482+03	\N	\N
5	4	Xiaomi Mi Populele 2 LED USB Smart Ukulele Black	Умное укулеле от Xiaomi	9500.00	3	/uploads/products/prod_684500939fb317.48100780.png	2025-06-08 06:16:35.655447+03	\N	\N
6	5	Stephan Weis AS-100G Альт-саксофон	Stephan Weis - это стройные, легко продуваемые инструменты с профессиональной механикой и по доступной цене.	31000.00	1	/uploads/products/prod_684501c577e311.80911928.png	2025-06-08 06:19:51.280055+03	\N	\N
7	1	Бас-гитара Clevan CPB-10-BK	Бас-гитара Clevan CPB-10-BK.\r\nКорпус: агатис.\r\nГриф: канадский клен.\r\nНакладка грифа: палисандр.	19500.00	4	/uploads/products/prod_6845022d74b6f5.31020069.png	2025-06-08 06:23:25.479218+03	\N	\N
\.


--
-- Data for Name: sbp_payments; Type: TABLE DATA; Schema: public; Owner: store_user
--

COPY public.sbp_payments (id, external_id, order_id, amount, status, created_at, updated_at) FROM stdin;
1	SBP-97f03f354f8d6be7	23	55000.00	paid	2025-12-02 22:25:53.755925+03	2025-12-02 22:26:45.215518+03
2	SBP-92342528e48ac0cb	24	19500.00	paid	2025-12-02 23:19:27.525188+03	2025-12-02 23:20:36.586291+03
3	SBP-f25ed85159929c4e	25	12000.00	pending	2025-12-03 00:55:55.115327+03	2025-12-03 00:55:55.115327+03
4	SBP-e9fd545b3d1a82df	26	19500.00	pending	2025-12-03 01:09:34.780946+03	2025-12-03 01:09:34.780946+03
5	SBP-5790e6102bc96859	27	12000.00	paid	2025-12-03 01:12:48.754841+03	2025-12-03 01:13:15.312077+03
6	SBP-c92a8f4895fc2e66	28	31000.00	pending	2025-12-03 01:29:20.386618+03	2025-12-03 01:29:20.386618+03
7	SBP-33de1d597e24bc6a	29	19500.00	pending	2025-12-03 02:10:46.986077+03	2025-12-03 02:10:46.986077+03
8	SBP-e3cac7efccfcfcd2	30	9500.00	pending	2025-12-03 02:16:25.408824+03	2025-12-03 02:16:25.408824+03
9	SBP-7148eb075c0dfafb	31	19500.00	pending	2025-12-03 02:56:40.659929+03	2025-12-03 02:56:40.659929+03
10	SBP-7633d2a9cb4050ef	32	8075.00	paid	2025-12-03 03:11:24.180256+03	2025-12-03 03:11:28.118063+03
11	SBP-b96b0934a5b593f9	33	16575.00	paid	2025-12-03 03:38:31.763549+03	2025-12-03 03:38:40.790544+03
12	SBP-258fa64d692391e7	34	19500.00	paid	2025-12-03 17:39:06.541662+03	2025-12-03 17:39:14.482618+03
13	SBP-6b24a35b66831cb1	36	16575.00	paid	2025-12-03 19:13:09.10594+03	2025-12-03 19:14:56.560168+03
14	SBP-28d88a76c583236e	37	31000.00	paid	2025-12-03 19:21:24.341734+03	2025-12-03 19:21:33.090837+03
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: store_user
--

COPY public.users (id, email, password_hash, name, is_admin, created_at) FROM stdin;
3	admin@example.com	$2y$12$45C8Br1eM502DLy4gCf3bOWYLKVKwsjqZ2ecGsm36v7e2CXKQQZdW	Admin	t	2025-06-08 05:19:55.279759+03
1	test1@example.com	$2y$12$lCZtah/M9s5yEfDQ2kSFV.v/kkMDVWwuYsTukGmLcpoMQWy2AD6I.	Александр	f	2025-06-08 04:27:36.718961+03
4	kratz_5@mail.ru	$2y$12$LnShqtPT.8CZJTFR8ANMCuzub1HtS/gMMJAFbHVvzlPu2XQl/wl0m	Михаил	f	2025-06-08 12:05:47.000223+03
5	test2@example.com	$2y$12$wGP9sWYKPk.6ARf1ptfZi.1U.BVZJ0yqAL49v/ZrZ8Xq5W1W9QptO	Дельта	f	2025-06-08 22:45:31.984244+03
6	abdyle@bk.ru	$2y$12$roZ2wm11q5eak.yShEgN3ud56UkcncQoKe2t1zAMAuHSr59D9CrCq	Михаил	t	2025-06-08 22:51:26.636923+03
\.


--
-- Name: categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: store_user
--

SELECT pg_catalog.setval('public.categories_id_seq', 5, true);


--
-- Name: coupons_id_seq; Type: SEQUENCE SET; Schema: public; Owner: store_user
--

SELECT pg_catalog.setval('public.coupons_id_seq', 2, true);


--
-- Name: order_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: store_user
--

SELECT pg_catalog.setval('public.order_items_id_seq', 43, true);


--
-- Name: orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: store_user
--

SELECT pg_catalog.setval('public.orders_id_seq', 37, true);


--
-- Name: products_id_seq; Type: SEQUENCE SET; Schema: public; Owner: store_user
--

SELECT pg_catalog.setval('public.products_id_seq', 7, true);


--
-- Name: sbp_payments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: store_user
--

SELECT pg_catalog.setval('public.sbp_payments_id_seq', 14, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: store_user
--

SELECT pg_catalog.setval('public.users_id_seq', 6, true);


--
-- Name: categories categories_name_key; Type: CONSTRAINT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_name_key UNIQUE (name);


--
-- Name: categories categories_pkey; Type: CONSTRAINT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_pkey PRIMARY KEY (id);


--
-- Name: coupons coupons_code_key; Type: CONSTRAINT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.coupons
    ADD CONSTRAINT coupons_code_key UNIQUE (code);


--
-- Name: coupons coupons_pkey; Type: CONSTRAINT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.coupons
    ADD CONSTRAINT coupons_pkey PRIMARY KEY (id);


--
-- Name: order_items order_items_pkey; Type: CONSTRAINT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_pkey PRIMARY KEY (id);


--
-- Name: orders orders_pkey; Type: CONSTRAINT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_pkey PRIMARY KEY (id);


--
-- Name: products products_pkey; Type: CONSTRAINT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_pkey PRIMARY KEY (id);


--
-- Name: sbp_payments sbp_payments_external_id_key; Type: CONSTRAINT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.sbp_payments
    ADD CONSTRAINT sbp_payments_external_id_key UNIQUE (external_id);


--
-- Name: sbp_payments sbp_payments_pkey; Type: CONSTRAINT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.sbp_payments
    ADD CONSTRAINT sbp_payments_pkey PRIMARY KEY (id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: idx_sbp_payments_external_id; Type: INDEX; Schema: public; Owner: store_user
--

CREATE INDEX idx_sbp_payments_external_id ON public.sbp_payments USING btree (external_id);


--
-- Name: idx_sbp_payments_order_id; Type: INDEX; Schema: public; Owner: store_user
--

CREATE INDEX idx_sbp_payments_order_id ON public.sbp_payments USING btree (order_id);


--
-- Name: order_items order_items_order_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_order_id_fkey FOREIGN KEY (order_id) REFERENCES public.orders(id);


--
-- Name: order_items order_items_product_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_product_id_fkey FOREIGN KEY (product_id) REFERENCES public.products(id);


--
-- Name: orders orders_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: products products_category_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_category_id_fkey FOREIGN KEY (category_id) REFERENCES public.categories(id);


--
-- Name: sbp_payments sbp_payments_order_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: store_user
--

ALTER TABLE ONLY public.sbp_payments
    ADD CONSTRAINT sbp_payments_order_id_fkey FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE CASCADE;


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: pg_database_owner
--

GRANT ALL ON SCHEMA public TO store_user;


--
-- PostgreSQL database dump complete
--


-- Least-privilege roles for PostgreSQL. Run after the migrations, as a
-- superuser or a role with CREATEROLE:
--
--   psql -d agenda_contabil -v owner=agenda \
--        -v app_password='...' -v reports_password='...' \
--        -f database/sql/privileges.sql
--
-- The owner keeps running migrations; the application connects as
-- agenda_app and reporting tools as agenda_reports. Safe to run again.

\set ON_ERROR_STOP on

BEGIN;

SELECT format('CREATE ROLE agenda_app LOGIN PASSWORD %L', :'app_password')
WHERE NOT EXISTS (SELECT FROM pg_roles WHERE rolname = 'agenda_app') \gexec

SELECT format('CREATE ROLE agenda_reports LOGIN PASSWORD %L', :'reports_password')
WHERE NOT EXISTS (SELECT FROM pg_roles WHERE rolname = 'agenda_reports') \gexec

ALTER ROLE agenda_app CONNECTION LIMIT 50;
ALTER ROLE agenda_reports CONNECTION LIMIT 5;

SELECT format('REVOKE ALL ON DATABASE %I FROM PUBLIC', current_database()) \gexec
SELECT format('GRANT CONNECT ON DATABASE %I TO %I, agenda_app, agenda_reports', current_database(), :'owner') \gexec

REVOKE CREATE ON SCHEMA public FROM PUBLIC;
GRANT USAGE ON SCHEMA public TO agenda_app, agenda_reports;

-- Application: data only, no DDL.
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO agenda_app;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO agenda_app;

-- History is append-only; clients and appointments are deactivated or
-- soft deleted, never removed. The triggers enforce the same rules even for
-- the owner; the grants make the attempt fail earlier.
REVOKE UPDATE, DELETE, TRUNCATE ON appointment_activities, data_audits FROM agenda_app;
REVOKE DELETE, TRUNCATE ON clients, appointments, users FROM agenda_app;
REVOKE ALL ON migrations FROM agenda_app;
GRANT SELECT ON migrations TO agenda_app;

-- Reports: the view without personal data, nothing else.
REVOKE ALL ON ALL TABLES IN SCHEMA public FROM agenda_reports;
GRANT SELECT ON appointment_facts TO agenda_reports;

-- Tables created by future migrations follow the same rule for the app.
ALTER DEFAULT PRIVILEGES FOR ROLE :"owner" IN SCHEMA public
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO agenda_app;
ALTER DEFAULT PRIVILEGES FOR ROLE :"owner" IN SCHEMA public
    GRANT USAGE, SELECT ON SEQUENCES TO agenda_app;

COMMIT;

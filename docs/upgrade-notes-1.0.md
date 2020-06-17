Upgrade notes for RciamStatsViewer 1.0
======================================

* For PostgreSQL >= 9.6.12 run:

    - `ALTER TABLE public.cm_rciam_stats_viewers ADD COLUMN IF NOT EXISTS privileged_co_group_id integer;`
    - `ALTER TABLE ONLY public.cm_rciam_stats_viewers DROP CONSTRAINT IF EXISTS cm_rciam_stats_viewers_privileged_co_group_id_fkey;`
    - `ALTER TABLE ONLY public.cm_rciam_stats_viewers ADD CONSTRAINT cm_rciam_stats_viewers_privileged_co_group_id_fkey FOREIGN KEY (privileged_co_group_id) REFERENCES public.cm_co_groups(id);`

* For PostgreSQL < 9.6.12 :

    - You must check manually if column "privileged_co_group_id" exists and if not run: `ALTER TABLE public.cm_rciam_stats_viewers ADD COLUMN privileged_co_group_id integer;`.
    - Finally, run:
`ALTER TABLE ONLY public.cm_rciam_stats_viewers DROP CONSTRAINT IF EXISTS cm_rciam_stats_viewers_privileged_co_group_id_fkey;`
`ALTER TABLE ONLY public.cm_rciam_stats_viewers ADD CONSTRAINT cm_rciam_stats_viewers_privileged_co_group_id_fkey FOREIGN KEY (privileged_co_group_id) REFERENCES public.cm_co_groups(id);`

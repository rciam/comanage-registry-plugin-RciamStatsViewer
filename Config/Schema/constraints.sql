--Add foreign keys and grant access to user
ALTER TABLE ONLY public.cm_rciam_stats_viewers ADD CONSTRAINT cm_rciam_stats_viewers_co_id_fkey FOREIGN KEY (co_id) REFERENCES public.cm_cos(id);
ALTER TABLE ONLY public.cm_rciam_stats_viewers ADD CONSTRAINT cm_rciam_stats_viewers_rciam_stats_viewer_id_fkey FOREIGN KEY (rciam_stats_viewer_id) REFERENCES public.cm_rciam_stats_viewers(id);
GRANT SELECT ON TABLE public.cm_rciam_stats_viewers TO cmregistryuser_proxy;
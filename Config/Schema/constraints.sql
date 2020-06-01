--Add foreign keys and grant access to user
ALTER TABLE ONLY public.cm_rciam_stats_viewers ADD CONSTRAINT cm_rciam_stats_viewers_co_id_fkey FOREIGN KEY (co_id) REFERENCES public.cm_cos(id);
ALTER TABLE ONLY public.cm_rciam_stats_viewers ADD CONSTRAINT cm_rciam_stats_viewers_rciam_stats_viewer_id_fkey FOREIGN KEY (rciam_stats_viewer_id) REFERENCES public.cm_rciam_stats_viewers(id);
ALTER TABLE ONLY public.cm_rciam_stats_viewers ADD CONSTRAINT cm_rciam_stats_viewers_privileged_co_group_id_fkey FOREIGN KEY (privileged_co_group_id) REFERENCES public.cm_co_groups(id);
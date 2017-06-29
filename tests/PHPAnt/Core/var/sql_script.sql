DROP TABLE IF EXISTS a;
DROP TABLE IF EXISTS b;

#Get all the from emails with their IDs
CREATE TEMPORARY TABLE a (
SELECT 
    unidentified_emails_id, unidentified_emails_from as unidentified_email
FROM
    mcdb2.unidentified_emails
GROUP BY unidentified_emails_from);

#Get all the to emails with their IDs.
INSERT INTO a (SELECT 
    unidentified_emails_id, unidentified_emails_to as unidentified_email
FROM
    mcdb2.unidentified_emails
GROUP BY unidentified_emails_to);

#Remove the "known" emails from table a, and store result set in b.
CREATE TEMPORARY TABLE b (SELECT unidentified_emails_id, unidentified_email FROM a WHERE a.unidentified_email NOT IN (SELECT emails_email FROM emails) GROUP BY unidentified_emails_id ORDER BY unidentified_emails_id);

#Insert those results into the captured_email_queue.
INSERT IGNORE INTO  captured_email_queue
    (SELECT null as captured_email_queue_id, unidentified_emails_from AS captured_email_queue_from, unidentified_emails_to AS captured_email_queue_to, unidentified_emails_hash AS captured_email_queue_hash, unidentified_emails_from_domain AS captured_email_queue_from_domain, unidentified_emails_to_domain AS captured_email_queue_to_domain
FROM
    unidentified_emails
WHERE
    unidentified_emails.unidentified_emails_id IN (SELECT 
            unidentified_emails_id
        FROM
            b));
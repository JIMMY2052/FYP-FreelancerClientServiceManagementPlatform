-- Fix existing gig-based agreements to use their gig's RevisionCount
-- This updates agreements that were created before the RemainingRevisions feature

-- Step 1: Update gig-based agreements by matching ProjectTitle with Gig Title
-- (This is a best-effort match - may not work if titles were modified)
UPDATE agreement a
INNER JOIN gig g ON a.ProjectTitle = g.Title
SET a.RemainingRevisions = g.RevisionCount
WHERE a.RemainingRevisions = 3  -- Only update those with default value
  AND g.RevisionCount IS NOT NULL;  -- Skip unlimited gigs

-- Step 2: Display what was updated
SELECT 
    a.AgreementID,
    a.ProjectTitle,
    a.RemainingRevisions as 'Updated Revisions',
    g.RevisionCount as 'Gig Revisions',
    CASE 
        WHEN a.RemainingRevisions = g.RevisionCount THEN 'Match ✓'
        ELSE 'Mismatch ✗'
    END as 'Status'
FROM agreement a
INNER JOIN gig g ON a.ProjectTitle = g.Title;

SELECT 'Existing gig-based agreements updated!' as Message;

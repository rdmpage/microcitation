# microcitation

Resolving "micro citations" to full bibliographic reference


Goal is to be able to take a simple three-part “micro citation” (journal, volume/year, page) and locate the corresponding publication (e.g., article) that includes that citation.

One approach is to build a database of article-level metadata, and do a lookup to find the article(s) with the page range that includes that page in the micro citation (for a given journal and volume). Care needs to be taken if a page can have more than one article (e.g., short notes), and in cases where article metadata includes a starting page but not the end page (common for CrossRef metadata for JSTOR articles). Hence we need various  strategies for harvesting sufficient metadata to accurately locate a micro citation.

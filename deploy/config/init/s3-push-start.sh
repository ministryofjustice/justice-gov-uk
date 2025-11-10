#!/bin/sh

export AWS_CLI_ARGS=""
# Truncate $IMAGE_TAG to 8 chars.
export IMAGE_TAG=$(echo $IMAGE_TAG | cut -c1-8)
export S3_DESTINATION="s3://$AWS_S3_BUCKET/build/$IMAGE_TAG"
export S3_LATEST="s3://$AWS_S3_BUCKET/builds/latest"
export S3_MANIFESTS="s3://$AWS_S3_BUCKET/build/manifests/"
export S3_MANIFEST="s3://$AWS_S3_BUCKET/build/manifests/$IMAGE_TAG.json"
export S3_SUMMARY="s3://$AWS_S3_BUCKET/build/manifests/summary.jsonl"
export TIMESTAMP=$(date +%s)


# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░
# 1️⃣ Function to handle errors
# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░

# Accepts 2 arguments, the return code of the aws command and the command itself.
# 0: The service responded with an HTTP response status code of 200 and there were 
#    no errors from either the CLI or the service the request was made to.
# 1: At least one or more s3 transfers failed for the command executed.
# 2: The meaning of this return code depends on the command being run.

catch_error() {
  if [ $1 -ne 0 ]; then
    echo "Error: command \`$2\` failed with return code $1"
    exit $1
  fi
}


# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░
# 2️⃣ Prepare CLI arguments
# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░

# If $AWS_ENDPOINT_URL is set and it's not an empty string, append to the AWS CLI args.
# This allows for localhost testing with minio.
if [ -n "$AWS_ENDPOINT_URL" ]; then
  export AWS_CLI_ARGS="$AWS_CLI_ARGS --endpoint-url $AWS_ENDPOINT_URL"
fi


# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░
# 3️⃣ Sync files to S3
# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░

echo "Syncing assets to $S3_DESTINATION ..."

# Sync the contents of the static folder to the s3 bucket
aws $AWS_CLI_ARGS s3 sync ./public $S3_DESTINATION
catch_error $? "aws s3 sync ./public $S3_DESTINATION"


# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░
# 4️⃣ Get a list of uploaded files
# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░

echo "Fetching list of uploaded files..."

# Get an array of all the files that were uploaded, remove "/build/$IMAGE_TAG" from the start.
UPLOADED_FILES=$(aws $AWS_CLI_ARGS s3 ls $S3_DESTINATION/ --recursive | awk '{print $4}')
catch_error $? "aws s3 ls $S3_DESTINATION/ --recursive"


# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░
# 5️⃣ Verify file counts
# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░

echo "Verifying file counts..."

# Verify that the number of files uploaded matches the number of files in the public folder.
# This is in addition to s3 sync's built-in verification.
LOCAL_FILES_COUNT=$(find ./public -type f | wc -l)
UPLOADED_FILES_COUNT=$(echo "$UPLOADED_FILES" | wc -l)

if [ $LOCAL_FILES_COUNT -ne $UPLOADED_FILES_COUNT ]; then
  echo "Error: The number of uploaded files ($UPLOADED_FILES_COUNT) does not match the number of local files ($LOCAL_FILES_COUNT)"
  exit 1
fi


# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░
# 6️⃣ Create latest build copy
# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░

echo "Creating latest build copy..."

# Create a copy of the current build at builds/latest for redirecting to.
# This uses S3-to-S3 copy which is faster than uploading from local again.
aws $AWS_CLI_ARGS s3 sync $S3_DESTINATION $S3_LATEST --delete
catch_error $? "aws s3 sync $S3_DESTINATION $S3_LATEST --delete"


# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░
# 7️⃣ Copy the list of uploaded files to S3
# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░

echo "Copying manifest to $S3_MANIFEST..."

# Use jq to parse the line-seperated $UPLOADED_FILES variable into a json array.
echo "$UPLOADED_FILES" | jq -R -s '{timestamp: '$TIMESTAMP', build: "'$IMAGE_TAG'", files: split("\n")[:-1]}' > ./mainfest.json

aws $AWS_CLI_ARGS s3 cp ./mainfest.json $S3_MANIFEST
catch_error $? "aws s3 cp ./mainfest.json $S3_MANIFEST"


# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░
# 8️⃣ Append this manifest to the summary
# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░

echo "Getting summary file..."

MANIFESTS_LS=$(aws $AWS_CLI_ARGS s3 ls $S3_MANIFESTS | awk '{print $4}')
catch_error $? "aws s3 ls $S3_MANIFESTS"

# Check if the summary file exists.
SUMMARY_EXISTS=$(echo "$MANIFESTS_LS" | grep -q "^summary.jsonl$" && echo "true" || echo "false")

if [ "$SUMMARY_EXISTS" = "true" ]; then
  echo "Summary file exists. Downloading..."
  aws $AWS_CLI_ARGS s3 cp $S3_SUMMARY ./summary.jsonl
  catch_error $? "aws s3 cp $S3_SUMMARY ./summary.jsonl"
else
  echo "Summary file does not exist. Creating..."
  touch ./summary.jsonl
fi

echo "Appending manifest to summary..."
echo '{"timestamp": '$TIMESTAMP', "build": "'$IMAGE_TAG'"}' >> ./summary.jsonl

echo "Copying summary to S3..."
aws $AWS_CLI_ARGS s3 cp --cache-control 'no-cache' ./summary.jsonl $S3_SUMMARY
catch_error $? "aws s3 cp ./summary.jsonl $S3_SUMMARY"


# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░
# 9️⃣ Manage the lifecycle of old builds
# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░

# Here we will:
# - Delete any builds that have been marked for deletion with the deleteAfter property
#   (so long as the current time is greater than the deleteAfter value).
# - Mark all builds except for the latest 5 deletion, with a deleteAfter property.

# An example of what will happen:
# - The application checks the summary file to see if it's build is still there.
# - Let's say it is, that value is cached for an hour.
# - We mark the build for deletion in the summary file (with deleteAfter property).
# - The application's cached value will expire before the build is deleted.
# - This way, the application's cached value is never incorrect.

# This function deletes a build from the S3 bucket, accepts a build tag as an argument
delete_build () {

  # 🅐 Remove the build from the summary file first.
  echo "Removing build $1 from $S3_SUMMARY..."

  cat ./summary.jsonl | jq -s -c 'map(select(.build != "'$1'")) .[]' > ./summary-tmp.jsonl
  catch_error $? "jq removing build from summary"
  mv ./summary-tmp.jsonl ./summary.jsonl

  # 🅑 Copy the revised summary to S3
  aws $AWS_CLI_ARGS s3 cp --cache-control 'no-cache' ./summary.jsonl $S3_SUMMARY
  catch_error $? "aws s3 cp ./summary.jsonl $S3_SUMMARY"

  # Next, delete the build folder from the S3 bucket.
  echo "Removing build $1 from s3://$AWS_S3_BUCKET/build/$1..."

  aws $AWS_CLI_ARGS s3 rm s3://$AWS_S3_BUCKET/build/$1 --recursive
  catch_error $? "aws s3 rm s3://$AWS_S3_BUCKET/build/$1 --recursive"


  # 🅒 Finally, remove the manifest file from the S3 bucket.
  echo "Removing manifest $1 from s3://$AWS_S3_BUCKET/build/manifests/$1.json..."

  aws $AWS_CLI_ARGS s3 rm s3://$AWS_S3_BUCKET/build/manifests/$1.json
  catch_error $? "aws s3 rm s3://$AWS_S3_BUCKET/build/manifests/$1.json"

  echo "Build $1 removed."
}

BUILDS_TO_DELETE=$(
  cat ./summary.jsonl |
  jq -s -c -r '
    # Identfy the entries where the deleteAfter property is set 
    # and the current time is greater than the deleteAfter value.
    map(select(.deleteAfter and .deleteAfter < '$TIMESTAMP')) |
    # Get unique values by build property
    unique_by(.build) |
    # Return only the build property
    map(.build)
    .[]
  '
)
catch_error $? "jq getting builds to delete"


if [ -z "$BUILDS_TO_DELETE" ]; then
  BUILDS_TO_DELETE_COUNT="0"
else
  BUILDS_TO_DELETE_COUNT=$(echo "$BUILDS_TO_DELETE" | wc -l)
  BUILDS_TO_DELETE_CSV=$(echo "$BUILDS_TO_DELETE" | tr '\n' ',' | sed 's/,$//')

  echo "Deleting the following builds: $BUILDS_TO_DELETE_CSV"

  for row in $BUILDS_TO_DELETE; do
    delete_build ${row}
  done
fi

# This function flags a build for deletion (with the deleteAfter property) in the summary file,
# it accepts a variable of build tags (seperated by line breaks) as an argument.

flag_builds () {

  echo "Marking the following builds for deletion: $BUILDS_TO_FLAG_CSV"

  # 🅐 Prepare a csv string to use in jq.
  BUILDS_TO_FLAG_CSV=$(echo $1 | tr '\n' ',' | sed 's/,$//')
  DELETE_AFTER=$(expr $TIMESTAMP + 86400) # 24 hours from now
  
  # 🅑 Use jq to transform the contents of summary.jsonl
  cat ./summary.jsonl | jq -s -c '
    map(
      if .build | IN ('$BUILDS_TO_FLAG_CSV') then
        . + {deleteAfter: '$DELETE_AFTER'}
      else
        .
      end
    )
    .[]
  ' > ./summary-tmp.jsonl
  catch_error $? "jq setting deleteAfter property"

  mv ./summary-tmp.jsonl ./summary.jsonl
  
  # 🅒 Copy the updated file to S3
  echo "Copying summary (with builds flagged for deletion) to S3..."
  aws $AWS_CLI_ARGS s3 cp --cache-control 'no-cache' ./summary.jsonl $S3_SUMMARY
  catch_error $? "aws s3 cp ./summary.jsonl $S3_SUMMARY"

}

# Get the oldest builds (excluding the newest 20), they will be flagged for deletion.
BUILDS_TO_FLAG=$(
  cat ./summary.jsonl |
  jq -s -c '
    unique_by(.build) |
    sort_by(.timestamp) |
    # Filter out entries where the deleteAfter property is already set
    map(select(.deleteAfter == null)) |
    # Get all but the last 20 builds
    .[:-20] |
    map(.build)
    .[]
  '
)
catch_error $? "jq getting builds to flag"

if [ -z "$BUILDS_TO_FLAG" ]; then
  BUILDS_TO_FLAG_COUNT="0"
else
  BUILDS_TO_FLAG_COUNT=$(echo "$BUILDS_TO_FLAG" | wc -l)
  flag_builds $BUILDS_TO_FLAG
fi


# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░
# 🔟 Report on actions taken
# ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░  ░░

echo "Assets pushed to:            $S3_DESTINATION"
echo "Latest build copied to:      $S3_LATEST"
echo "Manifest pushed to:          $S3_MANIFEST"
echo "Summary pushed to:           $S3_SUMMARY"
echo "Builds deleted:              $BUILDS_TO_DELETE_COUNT"
echo "Builds flagged for deletion: $BUILDS_TO_FLAG_COUNT"
